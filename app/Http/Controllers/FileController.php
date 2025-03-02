<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedFile;

class FileController extends Controller
{
//    public function __invoke(Request $request)
//    {
//        try {
//            $request->validate([
//                'file' => 'required|file|mimes:pdf,docx,csv,xml|max:10240',
//            ]);
//
//            if ($request->hasFile('file')) {
//                $file = $request->file('file');
//                $path = $file->store('uploads', 'public');
//
//                $uploadedFile = UploadedFile::create([
//                    'path' => $path,
//                    'original_name' => $file->getClientOriginalName(),
//                ]);
//
//                return response()->json([
//                    'message' => 'Файл успешно загружен',
//                    'original_name' => $file->getClientOriginalName(),
//                    'file_id' => $uploadedFile->id,
//                    'path' => $path,
//                ]);
//            }
//
//            return response()->json(['error' => 'Файл не был загружен'], 400);
//        } catch (\Exception $e) {
//            \Log::error('File upload error: ' . $e->getMessage());
//            return response()->json(['error' => $e->getMessage()], 500);
//        }
//    }

    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,docx,csv,xml|max:10240',
            'chat_id' => 'required|integer'
        ]);

        $chatId = $request->chat_id;
        $files = $request->file('files');

        // Initialize HTTP request
        $httpRequest = Http::asMultipart();

        // Attach each file to the request with the same parameter name
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $httpRequest = $httpRequest->attach(
                "files", // This name must match the parameter name in FastAPI
                file_get_contents($file->path()),
                $fileName
            );
        }

        // Send the request to FastAPI
        $response = $httpRequest->post("http://python:8000/files/{$chatId}");

        // Return the response from FastAPI
        return response()->json($response->json());
    }

    public function getFiles($chat_id)
    {
        $response = Http::get("http://python:8000/files/{$chat_id}");
        return $response;
    }

    public function preview($chat_id, $document_id)
    {
        // Отправляем GET-запрос к FastAPI
        $response = Http::get("http://python:8000/files/{$chat_id}/{$document_id}");

        // Проверяем, успешен ли ответ
        if ($response->successful()) {
            // Получаем содержимое ответа как бинарные данные
            $fileContent = $response->body();

            // Устанавливаем правильный заголовок Content-Type
            $contentType = $response->header('Content-Type');

            // Устанавливаем заголовок Content-Disposition для отображения в браузере
            return response($fileContent)
                ->header('Content-Type', $contentType);
        }

        // Если файл не найден, возвращаем ошибку
        return response()->json(['error' => 'File not found'], 404);
    }

    public function delete($chat_id, $document_id) {
        $response = Http::delete("http://python:8000/files/{$chat_id}/{$document_id}");

        return $response;
    }

}
