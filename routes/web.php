<?php

use App\Services\OllamaService;
use Illuminate\Support\Facades\Route;

// Главная страница приложения
Route::get('/', function () {
    return view('app');
});

Route::get('/chat/models', [OllamaService::class, 'getModels']);
Route::post('/chat/setModel', [OllamaService::class, 'setModel']);



// SPA-страница, если не определен URL
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
