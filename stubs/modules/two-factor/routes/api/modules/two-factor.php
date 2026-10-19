<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
    Route::post('two-factor/reset', [TwoFactorController::class, 'resetWithRecoveryCode']);
    Route::post('two-factor/send-reset-link', [TwoFactorController::class, 'sendResetLink']);
    Route::post('two-factor/reset/{id}/{hash}', [TwoFactorController::class, 'resetViaSignedLink'])
        ->middleware('signed')
        ->name('auth.2fa.reset-via-link');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::prefix('two-factor')->name('auth.2fa.')->group(function (): void {
            Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
            Route::post('confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
            Route::post('verify', [TwoFactorController::class, 'verify'])->name('verify');
            Route::post('recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('recovery-codes.show');
            Route::post('recovery-codes/regenerate', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
            Route::delete('/', [TwoFactorController::class, 'disable'])->name('disable');
        });
    });
});
