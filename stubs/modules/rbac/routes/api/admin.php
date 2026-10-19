<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    // Add application admin routes here.
});
