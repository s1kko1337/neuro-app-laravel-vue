<?php


namespace App\Http\Controllers\Voice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Factories\TtsServiceFactory;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioController extends Controller
{
    public function getAudio(Request $request, $path)
    {
        $filePath = storage_path('app/public/chat_audio/' . $path);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found', 'path' => $filePath], 404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'audio/wav',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
}
