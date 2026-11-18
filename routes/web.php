<?php

declare(strict_types=1);

use App\Enums\ErrorCode;
use App\Http\Controllers\Docs\ErrorCatalogueController;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('docs.errors'));

Route::get('docs/errors', [ErrorCatalogueController::class, 'index'])->name('docs.errors');

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return ApiResponse::error(
            ErrorCode::RESOURCE_NOT_FOUND,
            'Resource not found.',
            404,
            'The requested resource does not exist.'
        );
    }

    return redirect()->route('docs.errors');
});
