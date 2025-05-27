<?php

use Illuminate\Support\Facades\Route;

Route::get('/chat/history', [\App\Http\Controllers\Voice\UserSurveyChatMessagesController::class, 'history']);

Route::get('/survey', [\App\Http\Controllers\Voice\UserSurveyChatMessagesController::class, 'survey']);
Route::post('/survey', [\App\Http\Controllers\Voice\UserSurveyChatMessagesController::class, 'userStore']);

Route::get('/chat_audio/{path}', [\App\Http\Controllers\Voice\AudioController::class, 'getAudio'])
    ->where('path', '.*')
    ->name('audio.file');
