<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:auth'])->prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::get('user', [AuthController::class, 'user']);
            Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail']);
            Route::post('refresh', [AuthController::class, 'refresh']);

            // Profile Management
            Route::put('profile', [\App\Http\Controllers\Api\Auth\ProfileController::class, 'update']);
            Route::put('profile/password', [\App\Http\Controllers\Api\Auth\ProfileController::class, 'updatePassword']);
        });

        Route::post('password/email', [AuthController::class, 'forgotPassword']);
        Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');

        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
    });
});
