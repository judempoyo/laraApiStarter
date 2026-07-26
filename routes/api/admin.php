<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\ImpersonationController;
use Illuminate\Support\Facades\Route;

/**
 * Admin-only routes.
 *
 * These routes are loaded from routes/api.php with the /api/v1 prefix.
 * All routes here require: auth via the configured guard + role:admin.
 *
 * Register your admin-specific resource routes in this file.
 *
 * Example:
 *   Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
 */
Route::middleware(['auth:' . config('api.auth_guard', 'sanctum'), 'throttle:api', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // ── Stats example (replace with your real admin routes) ────────────
        Route::get('stats', function () {
            return \App\Http\Responses\ApiResponse::success(null, 'Admin stats access granted.');
        })->name('stats');

        // ── Impersonation ───────────────────────────────────────────────────
        Route::post('impersonate/{userId}', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::delete('impersonate', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

        // ── Add admin resource routes below ─────────────────────────────────
    });
