<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\Auth\SocialiteController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\EnumController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

$guard = config('api.auth_guard', 'sanctum');

Route::prefix('v1')->group(function () use ($guard): void {

    // ─── Public Auth ───────────────────────────────────────────────────────
    Route::prefix('auth')->middleware('throttle:auth')->group(function () use ($guard): void {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('password/email', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
        Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
        Route::post('check-email', [AuthController::class, 'checkEmail']);
        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

        // ── Google OAuth ───────────────────────────────────────────────────
        Route::get('google/redirect', [SocialiteController::class, 'redirectToGoogle']);
        Route::get('google/callback', [SocialiteController::class, 'handleGoogleCallback']);

        // ── Authenticated Auth Actions ─────────────────────────────────────
        Route::middleware(["auth:{$guard}", 'throttle:api'])->group(function () use ($guard): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('user', [AuthController::class, 'user']);
            Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail']);

            // ── Sessions ───────────────────────────────────────────────────
            Route::prefix('sessions')->name('auth.sessions.')->group(function (): void {
                Route::get('/', [SessionController::class, 'index'])->name('index');
                Route::delete('/others', [AuthController::class, 'logoutOthers'])->name('logout-others');
                Route::delete('/{tokenId}', [AuthController::class, 'logoutSession'])->name('logout-session');
            });

            // ── Profile ────────────────────────────────────────────────────
            Route::prefix('profile')->name('auth.profile.')->group(function (): void {
                Route::patch('/', [ProfileController::class, 'update'])->name('update');
                Route::patch('/email', [ProfileController::class, 'changeEmail'])->name('change-email');
                Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
                Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
                Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
            });

            // ── Two-Factor Authentication ───────────────────────────────────
            Route::prefix('two-factor')->name('auth.2fa.')->group(function (): void {
                Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
                Route::post('confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
                Route::post('verify', [TwoFactorController::class, 'verify'])->name('verify');
                Route::delete('/', [TwoFactorController::class, 'disable'])->name('disable');
            });
        });
    });

    // ─── Public Utility ────────────────────────────────────────────────────
    Route::get('enums/{enum}', [EnumController::class, 'show']);
    Route::get('health', HealthController::class);

    // ─── Optional Auth (guest + authenticated) ─────────────────────────────
    Route::middleware('optional.auth')->group(function (): void {
        // Routes accessible to both guests and authenticated users.
        // Use request()->user() to conditionally change behavior.
    });

    // ─── API Key Test ───────────────────────────────────────────────────────
    // Accepts BOTH authentication methods (try-first order):
    //   1. Sanctum Bearer token  →  Authorization: Bearer <token>
    //   2. API Key               →  X-API-Key: <key>  |  Authorization: <key>
    // DELETE this route block once your integration is validated.
    Route::middleware("auth:{$guard},api-key")->group(function (): void {
        Route::get('test-api-key', function () {
            return \App\Http\Responses\ApiResponse::success([
                'user'       => auth()->user()->only('id', 'name', 'email'),
                'guard_used' => auth()->getDefaultDriver(),
            ], 'Authenticated successfully.');
        })->name('test.api-key');
    });

    // ─── Role-based route files ────────────────────────────────────────────
    require __DIR__ . '/api/admin.php';
    require __DIR__ . '/api/user.php';
});
