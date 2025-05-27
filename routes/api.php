<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use \App\Http\Controllers\ChatController;

use App\Http\Controllers\FileController;
use App\Http\Controllers\GenerateContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Models\UploadedFile;

// Публичные маршруты
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Сброс и изменение пароля
    Route::post('forgot-password', [PasswordResetController::class, 'revoke']);
    Route::get('reset-password/{token}', [PasswordResetController::class, 'invoke'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// Маршруты, требующие аутентификации, но не требующие верифицированного email
Route::prefix('v1')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Маршруты для верификации email
    // Проверка статуса верификации
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    // Верификация email по ссылке из письма
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // Повторная отправка письма для верификации
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:25,1')
        ->name('verification.send');
});


// Маршруты, требующие верифицированного email
Route::prefix('v1')->middleware(['throttle:api', 'auth:sanctum', 'verified', 'can:use-Api'])->group(function () {
    Route::get('/user', static function (Request $request) {
        return $request->user();
    });
    Route::post('password/change', [PasswordResetController::class, 'change']);

    Route::get('models', function() {
        $response = Http::get('http://python:8000/llm/models');

        if ($response) {
            return $response;
        } else {
            return response()->json(['error' => 'Unable to fetch data'], 404);
        }
    });

    Route::get('chats', [ChatController::class, 'getChats']);

    Route::delete('chats/{chatId}', [ChatController::class, 'destroy']);

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
});


