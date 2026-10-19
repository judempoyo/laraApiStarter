<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
    Route::get('google/redirect', [SocialiteController::class, 'redirectToGoogle']);
    Route::get('google/callback', [SocialiteController::class, 'handleGoogleCallback']);
});
