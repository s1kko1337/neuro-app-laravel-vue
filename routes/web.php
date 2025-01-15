<?php

use Illuminate\Support\Facades\Route;

// Главная страница приложения
Route::get('/', function () {
    return view('app');
});















// SPA-страница, если не определен URL
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
