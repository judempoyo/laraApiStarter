<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ApiKeyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
});
