<?php

use App\Http\Controllers\AskController;
use \App\Http\Controllers\ChatController;

use App\Http\Controllers\FileController;
use App\Http\Controllers\GenerateContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Models\UploadedFile;

Route::get('models', function() {
    $response = Http::get('http://python:8000/llm/models');

    if ($response) {
        return $response;
    } else {
        return response()->json(['error' => 'Unable to fetch data'], 404);
    }
});
#Route::get('models', [\App\Services\OllamaService::class, 'getModels']);
Route::get('chats', [ChatController::class, 'getChats']);
Route::post('createChat', [ChatController::class, 'addChat']);
# Получение сообщений чата
Route::get('chats/{chatId}/messages', function($chatId) {
    // Используем переменную $chatId в URL
    $response = Http::get("http://python:8000/chats/{$chatId}/messages");

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});
# Добавление сообщения в чат
Route::post('chats/{chatId}/messages', [ChatController::class, 'send_message'])->name('chats.send_message');
# Получение сообщения чата
Route::get('chats/{chatId}/messages/{messageId}', function($chatId, $messageId) {
    // Используем переменную $chatId в URL
    $response = Http::get("http://python:8000/chats/{$chatId}/messages/{$messageId}");

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});

Route::post('/upload', function(Request $request) {
    $request->validate([
        'file' => 'required|file|mimes:pdf,docx,csv,xml|max:10240',
    ]);
    
    $file = $request->file('file');
    $file->getClientOriginalName();

    $response = Http::attach(
        'file', 
        file_get_contents($file->path()),
        $file->getClientOriginalName()
    )->post('http://python:8000/files');
    
    if ($response->successful()) {
        UploadedFile::create([
            'original_name' => $response->json()['original_name'],
            'path' => "uploads/". $response->json()['document_id'] . $response->json()['file_extension'],
        ]);
        
        return $response->json();
    }
    
    return response()->json(['error' => 'Ошибка обработки файла'], 500);
});

// Route::post('/upload', [FileController::class, '__invoke']);

Route::post('/generate-context', [GenerateContextController::class, '__invoke']);


