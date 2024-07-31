<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('google/signup', [AuthController::class, 'redirectToGoogleSignUp']);
    Route::get('google/signin', [AuthController::class, 'redirectToGoogleSignIn']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});
