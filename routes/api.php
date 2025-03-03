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
# Работа с файлами
Route::post('/files/{chat_id}', [FileController::class, 'upload']);
Route::get('/files/{chat_id}', [FileController::class, 'getFiles']);
Route::get('/files/{chat_id}/{document_id}', [FileController::class, 'preview']);
Route::delete('/files/{chat_id}/{document_id}', [FileController::class, 'delete']);


Route::post('/collection/create', [GenerateContextController::class, 'create']);
Route::delete('/collection/{chat_id}', [GenerateContextController::class, 'delete']);

