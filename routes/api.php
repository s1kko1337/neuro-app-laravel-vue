<?php

use App\Http\Controllers\AskController;
use \App\Http\Controllers\ChatController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('ask',[AskController::class, '__invoke']);
Route::get('models', [ChatController::class, '__invoke']);
