<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ApiKeyController;
use App\Http\Controllers\Api\V1\User\NotificationController;
use App\Http\Controllers\Api\V1\User\PreferenceController;
use Illuminate\Support\Facades\Route;

/**
 * Authenticated user routes (any role).
 *
 * These routes are loaded from routes/api.php with the /api/v1 prefix.
 * All routes here require authentication via the configured guard.
 *
 * Register your user-specific resource routes in this file.
 *
 * Example:
 *   Route::apiResource('orders', \App\Http\Controllers\Api\User\OrderController::class);
 */
Route::middleware(['auth:' . config('api.auth_guard', 'sanctum'), 'throttle:api'])
    ->prefix('user')
    ->name('user.')
    ->group(function (): void {

        // ── User Preferences ────────────────────────────────────────────────
        Route::get('preferences', [PreferenceController::class, 'index'])->name('preferences.index');
        Route::put('preferences/{key}', [PreferenceController::class, 'set'])->name('preferences.set');
        Route::delete('preferences/{key}', [PreferenceController::class, 'destroy'])->name('preferences.destroy');

        // ── Notifications ───────────────────────────────────────────────────
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // ── API Keys ────────────────────────────────────────────────────────
        Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

        // ── Add user resource routes below ──────────────────────────────────
    });
