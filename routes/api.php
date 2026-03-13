<?php

use App\Http\Controllers\ShortenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::resource('shorten', ShortenController::class)->only(['store'])
    ->middleware('access.token.auth');
