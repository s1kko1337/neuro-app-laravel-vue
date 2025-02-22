<?php

use App\Http\Controllers\AskController;
use \App\Http\Controllers\ChatController;

use App\Http\Controllers\FileController;
use App\Http\Controllers\GenerateContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('ask',[AskController::class, '__invoke']);
Route::get('models', [ChatController::class, '__invoke']);
Route::post('chat', [ChatController::class, 'send']);
Route::get('chats', [ChatController::class, 'getChats']);
Route::post('createChat', [ChatController::class, 'addChat']);
#Route::get('chats/{chatId}/messages', [ChatController::class, 'getChatMessages']);
Route::get('chats/{chatId}/messages', function($chatId) {
    // Используем переменную $chatId в URL
    $response = Http::get("http://python:8000/chats/{$chatId}/messages");

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});
Route::get('chats/{chatId}/messages/{messageId}', function($chatId, $messageId) {
    // Используем переменную $chatId в URL
    $response = Http::get("http://python:8000/chats/{$chatId}/messages/{$messageId}");

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});
Route::post('/upload', [FileController::class, '__invoke']);
Route::post('/generate-context', [GenerateContextController::class, '__invoke']);


