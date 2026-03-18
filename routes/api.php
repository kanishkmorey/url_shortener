<?php

use App\Http\Controllers\ClicksController;
use App\Http\Controllers\ShortenController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('shorten', [ShortenController::class, 'store'])
    ->middleware('access.token.auth');

Route::resource('url', ShortenController::class)->only(['index', 'show', 'update', 'destroy'])
    ->middleware('access.token.auth');

Route::resource('/clicks', ClicksController::class)->only(['index', 'destroy'])
    ->middleware('access.token.auth');
