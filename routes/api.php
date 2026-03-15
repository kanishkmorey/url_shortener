<?php

use App\Http\Controllers\ClicksController;
use App\Http\Controllers\ShortenController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::resource('shorten', ShortenController::class)->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware('access.token.auth');

Route::resource('/clicks', ClicksController::class)->only(['index', 'delete'])
    ->middleware('access.token.auth');
