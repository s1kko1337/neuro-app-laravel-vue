<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-ollama', [GeneralController::class, 'testOllama']);
Route::get('/index', [GeneralController::class, 'index']);
