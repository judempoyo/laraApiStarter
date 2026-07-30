<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ApiKeyController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\User\NotificationController;
use App\Http\Controllers\Api\V1\User\PreferenceController;
use Illuminate\Support\Facades\Route;

/**
 * Authenticated user routes (any role).
 *
 * These routes are loaded from routes/api.php with the /api/v1 prefix.
 * All routes here require authentication via the configured guard.
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

        // ── Webhooks ────────────────────────────────────────────────────────
        Route::get('webhooks/events', [WebhookController::class, 'events'])->name('webhooks.events');
        Route::get('webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::patch('webhooks/{id}', [WebhookController::class, 'update'])->name('webhooks.update');
        Route::delete('webhooks/{id}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
        Route::get('webhooks/{id}/deliveries', [WebhookController::class, 'deliveries'])->name('webhooks.deliveries');
        Route::post('webhooks/{webhookId}/deliveries/{deliveryId}/redeliver', [WebhookController::class, 'redeliver'])->name('webhooks.redeliver');

        // ── Media ───────────────────────────────────────────────────────────
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::get('media/{id}/url', [MediaController::class, 'url'])->name('media.url');
        Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

        // ── Exports ─────────────────────────────────────────────────────────
        Route::get('exports/resources', [ExportController::class, 'resources'])->name('exports.resources');
        Route::get('exports', [ExportController::class, 'index'])->name('exports.index');
        Route::post('exports', [ExportController::class, 'store'])->name('exports.store');
        Route::get('exports/{id}', [ExportController::class, 'show'])->name('exports.show');
    });
