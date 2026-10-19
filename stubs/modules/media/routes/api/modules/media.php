<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AvatarController;
use App\Http\Controllers\Api\V1\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('user')->name('user.')->group(function (): void {
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::get('media/{id}/url', [MediaController::class, 'url'])->name('media.url');
    Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('auth/profile')->name('auth.profile.')->group(function (): void {
    Route::post('avatar', [AvatarController::class, 'uploadAvatar'])->name('upload-avatar');
    Route::delete('avatar', [AvatarController::class, 'deleteAvatar'])->name('delete-avatar');
});
