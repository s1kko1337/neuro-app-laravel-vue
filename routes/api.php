<?php

use App\Http\Controllers\AskController;
use \App\Http\Controllers\ModelsController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('ask',[AskController::class, '__invoke']);
Route::get('models', [ModelsController::class, '__invoke']);
