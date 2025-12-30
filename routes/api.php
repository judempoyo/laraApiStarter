<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
            Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });

        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
    });
});
