<?php

declare(strict_types=1);

use App\Http\Controllers\Docs\ErrorCatalogueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('docs.errors'));

Route::get('docs/errors', [ErrorCatalogueController::class, 'index'])->name('docs.errors');

