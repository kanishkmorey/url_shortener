<?php

use App\Http\Controllers\ShortenController;
use Illuminate\Support\Facades\Route;

Route::get('/{code}', [ShortenController::class, 'redirect'])
    ->withoutMiddleware('web');
