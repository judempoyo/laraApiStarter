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
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ])->append([
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ])->encryptCookies(except: [
            'scramble_access_key',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(ErrorCode::UNAUTHENTICATED, 'Unauthenticated.', 401, 'You are not authenticated.');
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

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(ErrorCode::NOT_FOUND, 'Resource not found.', 404, 'The requested resource could not be found.');
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 422,
                    'success' => false,
                    'error' => [
                        'code' => ErrorCode::VALIDATION_FAILED->value,
                        'message' => 'Validation failed.',
                        'details' => $e->errors(),
                    ],
                    'message' => 'The given data was invalid.',
                    'data' => null,
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(ErrorCode::FORBIDDEN, 'Access denied.', 403, $e->getMessage() ?: 'You do not have permission to access this resource.');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(ErrorCode::METHOD_NOT_ALLOWED, 'Method not allowed.', 405, $e->getMessage() ?: 'The HTTP method is not allowed for this route.');
            }
        });

        // Optional generic Throwable catch-all if required (excluding explicitly handled)
        // Leaving it to default for now to leverage Laravel's exception view during dev.
    })->create();
