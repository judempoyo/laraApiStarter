<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\User\PreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('preferences', [PreferenceController::class, 'index'])->name('preferences.index');
    Route::put('preferences/{key}', [PreferenceController::class, 'set'])->name('preferences.set');
    Route::delete('preferences/{key}', [PreferenceController::class, 'destroy'])->name('preferences.destroy');
});
