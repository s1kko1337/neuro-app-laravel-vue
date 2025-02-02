<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

// Главная страница приложения
Route::get('/', function () {
    return view('app');
});

Route::get('/files/{id}/preview', [FileController::class, 'preview']);


// SPA-страница, если не определен URL
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
