<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('webhooks/events', [WebhookController::class, 'events'])->name('webhooks.events');
    Route::get('webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::patch('webhooks/{id}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('webhooks/{id}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::get('webhooks/{id}/deliveries', [WebhookController::class, 'deliveries'])->name('webhooks.deliveries');
    Route::post('webhooks/{webhookId}/deliveries/{deliveryId}/redeliver', [WebhookController::class, 'redeliver'])->name('webhooks.redeliver');
});
