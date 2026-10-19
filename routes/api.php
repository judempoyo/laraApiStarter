<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\EnumController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

$guard = config('api.auth_guard', 'sanctum');

Route::prefix(strtolower(config('api.version', 'v1')))->group(function () use ($guard): void {

    // ─── Public Auth ───────────────────────────────────────────────────────
    Route::prefix('auth')->middleware('throttle:auth')->group(function () use ($guard): void {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('password/email', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
        Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
        Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        // ── Authenticated Auth Actions ─────────────────────────────────────
        Route::middleware(["auth:{$guard}", 'throttle:api'])->group(function (): void {
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
            });

        });
    });

    // ─── Public Utility ────────────────────────────────────────────────────
    Route::get('enums/{enum}', [EnumController::class, 'show']);
    Route::get('health', HealthController::class);

    foreach (glob(__DIR__.'/api/modules/*.php') ?: [] as $moduleRoutes) {
        require $moduleRoutes;
    }
});
