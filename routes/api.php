<?php

use App\Http\Controllers\ClicksController;
use App\Http\Controllers\ShortenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['access.token.auth', 'throttle:api'])->group(function () {
    Route::post('shorten', [ShortenController::class, 'store']);
    Route::resource('url', ShortenController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::get('url/{url}/stat', [ShortenController::class, 'getUrlStats']);
    Route::resource('/clicks', ClicksController::class)->only(['index', 'destroy']);
});

Route::middleware('throttle:redirect')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });
});
