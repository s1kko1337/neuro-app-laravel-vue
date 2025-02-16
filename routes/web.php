<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Главная страница приложения
Route::get('/', function () {
    return view('app');
});

Route::get('/files/{id}/preview', [FileController::class, 'preview']);


# Перенаправление запроса на API python
Route::get('/getInfo', function () {
    $response = Http::get('http://python:8000/getInfo');

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});


// SPA-страница, если не определен URL
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');


