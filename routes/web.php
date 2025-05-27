<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Главная страница приложения
Route::get('/', function () {
    return view('app');
});


# Перенаправление запроса на API python
Route::get('/getInfo', function () {
    $response = Http::get('http://python:8000/metrics');

    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'Unable to fetch data'], $response->status());
    }
});

Route::get('/login', function () {
    return view('app');
})->name('login');

// Маршруты для страниц верификации email
Route::get('/email/verify', function () {
    return view('app');
});

Route::get('/email/verify/{id}/{hash}', function () {
    return view('app');
});

// Все остальные запросы направляем в SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Маршруты для сброса пароля
Route::post('/forgot-password', function () {
    return view('app');
});

Route::get('/reset-password/{token}', function () {
    return view('app');
});

