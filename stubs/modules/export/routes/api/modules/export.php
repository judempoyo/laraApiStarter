<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('exports/resources', [ExportController::class, 'resources'])->name('exports.resources');
    Route::get('exports', [ExportController::class, 'index'])->name('exports.index');
    Route::post('exports', [ExportController::class, 'store'])->name('exports.store');
    Route::get('exports/{id}', [ExportController::class, 'show'])->name('exports.show');
});
