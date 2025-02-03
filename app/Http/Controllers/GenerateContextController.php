<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFile;
use App\Models\Chat;
use App\Models\Message;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class GenerateContextController extends Controller
{
    protected $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function __invoke(Request $request)
    {
        try {
            $request->validate([
                'file_ids' => 'required|array',
                'chat_id' => 'required|exists:chats,id',
            ]);

            $fileIds = $request->input('file_ids');
            $chatId = $request->input('chat_id');
            $chunks = [];
            $chat = Chat::findOrFail($chatId);
            $totalChunks = 0;

            foreach ($fileIds as $fileId) {
                $uploadedFile = UploadedFile::findOrFail($fileId);
                $filePath = storage_path('app/' . $uploadedFile->path);
                $text = $this->generateTextFromDoc($filePath);

                $fileChunks = $this->ollamaService->splitTextIntoChunks($text);
                $totalChunks += count($fileChunks);

                foreach ($fileChunks as $chunk) {
                    $message = $chat->messages()->create([
                        'role' => 'system',
                        'content' => $chunk,
                    ]);

                    $embedding = $this->ollamaService->generateEmbedding($chunk);
                    $message->embedding()->create([
                        'embedding' => $embedding,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Контекст успешно сгенерирован',
                'chat_id' => $chat->id,
                'chunks_count' => $totalChunks
            ]);
        } catch (\Exception $e) {
            Log::error('Generate context error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateTextFromDoc($filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match (strtolower($extension)) {
            'pdf' => $this->extractTextFromPdf($filePath),
            'docx', 'doc' => $this->extractTextFromWord($filePath),
            'csv' => $this->extractTextFromCsv($filePath),
            'xml' => $this->extractTextFromXml($filePath),
            default => throw new \Exception("Unsupported file format: $extension"),
        };
    }

    private function extractTextFromPdf($filePath): string
    {
        $parser = new PdfParser();
        try {
            $pdf = $parser->parseFile($filePath);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse PDF file: " . $e->getMessage());
        }
        return $pdf->getText();
    }

    private function extractTextFromWord($filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }
        return trim($text);
    }

    private function extractTextFromCsv($filePath): string
    {
        $text = '';
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $text .= implode(' ', $data) . ' ';
            }
            fclose($handle);
        }
        return trim($text);
    }

    private function extractTextFromXml($filePath): string
    {
        $xml = simplexml_load_file($filePath);
        return trim(strip_tags($xml->asXML()));
    }
}
