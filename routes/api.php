<?php

use App\Http\Controllers\AskController;
use \App\Http\Controllers\ChatController;

use App\Http\Controllers\FileController;
use App\Http\Controllers\GenerateContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('ask',[AskController::class, '__invoke']);
Route::get('models', [ChatController::class, '__invoke']);
Route::post('chat', [ChatController::class, 'send']);
Route::get('chats', [ChatController::class, 'getChats']);
Route::post('createChat', [ChatController::class, 'addChat']);
Route::get('chats/{chatId}/messages', [ChatController::class, 'getChatMessages']);
Route::post('/upload', [FileController::class, '__invoke']);
Route::post('/generate-context', [GenerateContextController::class, '__invoke']);
