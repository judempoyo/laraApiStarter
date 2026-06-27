<?php

declare(strict_types=1);

use App\Enums\ErrorCode;
use App\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'verified.email'     => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'optional.auth' => App\Http\Middleware\OptionalSanctumAuth::class,
        ])->append([
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ])->encryptCookies(except: [
            'docs_access_key',
        ]);
    })
    ->booted(function () {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower($request->input('email')) . '|' . $request->ip()
            );
        });
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

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            if ($request->is('/api/auth/*')) {
                return;
            }
            return ApiResponse::error(
                ErrorCode::RESOURCE_NOT_FOUND,
                'Resource not found.',
                404,
                'The requested resource does not exist'

            );
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code'    => 422,
                    'success' => false,
                    'error'   => [
                        'code'    => ErrorCode::VALIDATION_FAILED->value,
                        'message' => 'Validation failed.',
                        'details' => $e->errors(),
                    ],
                    'message' => 'The given data was invalid.',
                    'data'    => null,
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

        $exceptions->render(function (ApiException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    $e->errorCode,
                    $e->getMessage(),
                    $e->getStatusCode(),
                    $e->userMessage,
                    $e->errors,
                );
            }
        });

    })->create();
