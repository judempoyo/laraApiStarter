<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\EnumController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('password/email', [AuthController::class, 'forgotPassword']);
        Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
        Route::post('check-email', [AuthController::class, 'checkEmail']);
        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

        // ── Google OAuth ──
        Route::get('google/redirect', [SocialiteController::class, 'redirectToGoogle']);
        Route::get('google/callback', [SocialiteController::class, 'handleGoogleCallback']);

        Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('user', [AuthController::class, 'user']);
            Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail']);

            // ── Sessions ──
            Route::prefix('sessions')->group(function () {
                Route::get('/', [SessionController::class, 'index']);
                Route::delete('/others', [AuthController::class, 'logoutOthers']);
                Route::delete('/{tokenId}', [AuthController::class, 'logoutSession']);
            });

            // ── Profile ──
            Route::prefix('profile')->group(function () {
                Route::patch('/', [ProfileController::class, 'update']);
                Route::patch('/email', [ProfileController::class, 'changeEmail']);
                Route::put('/password', [ProfileController::class, 'updatePassword']);
                Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
                Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
            });
        });
    });

    Route::get('enums/{enum}', [EnumController::class, 'show']);

    // Health check
    Route::get('health', HealthController::class);

    // ─── v1 Resource Routes ──────────────────────────────────────
    // Register your API resource routes here, e.g.:
    // Route::apiResource('products', \App\Http\Controllers\Api\v1\ProductController::class);

    /**
     * Routes within this group allow both authenticated users and guests.
     * The 'optional.auth' middleware resolves the user if a valid token is present,
     * but does not block the request if the user is unauthenticated.
     */
    Route::middleware('optional.auth')->group(function () {
        //
    });

    // RBAC Examples
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Admins only
        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::get('stats', function () {
                return response()->json(['message' => 'Admin stats access granted.']);
            });
        });
    });
});
