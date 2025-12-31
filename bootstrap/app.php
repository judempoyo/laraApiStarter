<?php

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified.email' => \App\Http\Middleware\EnsureEmailIsVerifiedJson::class,
        ])->append([
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(ErrorCode::UNAUTHENTICATED, 'Unauthenticated.', 401, 'You are not authentificated');
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    ErrorCode::RATE_LIMIT_EXCEEDED,
                    'Too many requests.',
                    429,
                    'You have exceeded your request limit. Please try again later.'
                );
            }
        });
    })->create();
