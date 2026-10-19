<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('imports/resources', [ImportController::class, 'resources'])->name('imports.resources');
    Route::get('imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('imports', [ImportController::class, 'store'])->name('imports.store');
    Route::get('imports/{id}', [ImportController::class, 'show'])->name('imports.show');
});
