<?php

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
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
         $exceptions->render(function (AuthenticationException $e, $request) {
        if ($request->expectsJson()) {
            return ApiResponse::error('Unauthenticated.', 401);
        }
    });
    })->create();
