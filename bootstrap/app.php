<?php

declare(strict_types=1);

use App\Enums\ErrorCode;
use App\Exceptions\ApiException;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified.email' => EnsureEmailIsVerified::class,
        ])->append([
            ForceJsonResponse::class,
            SetLocale::class,
            RequestIdMiddleware::class,
        ])->encryptCookies(except: [
            'docs_access_key',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldRenderJson = fn (Request $request): bool => $request->expectsJson() || $request->is('api/*');

        $exceptions->render(function (ApiException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(
                    $e->errorCode,
                    $e->getMessage(),
                    $e->getStatusCode(),
                    $e->userMessage,
                    $e->errors,
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(ErrorCode::UNAUTHENTICATED, 'Unauthenticated.', 401, 'You are not authenticated.');
            }
        });

        $exceptions->render(function (AccessDeniedHttpException|AuthorizationException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(ErrorCode::FORBIDDEN, 'Access denied.', 403, 'You do not have permission to perform this action.');
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(ErrorCode::RESOURCE_NOT_FOUND, 'Resource not found.', 404, 'The requested resource does not exist.');
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(ErrorCode::METHOD_NOT_ALLOWED, 'Method not allowed.', 405, $e->getMessage() ?: 'The HTTP method is not allowed for this route.');
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                $formattedErrors = collect($e->errors())->map(fn ($messages) => implode(', ', $messages))->toArray();

                return ApiResponse::error(
                    ErrorCode::VALIDATION_FAILED,
                    'Validation failed',
                    422,
                    $e->validator->errors()->first(),
                    $formattedErrors
                );
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                return ApiResponse::error(
                    ErrorCode::RATE_LIMIT_EXCEEDED,
                    'Too many requests.',
                    429,
                    'You have exceeded your request limit. Please try again later.'
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request) && $e->getStatusCode() === 403) {
                return ApiResponse::error(ErrorCode::FORBIDDEN, 'Access denied.', 403, 'You do not have permission to perform this action.');
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($shouldRenderJson) {
            if ($shouldRenderJson($request)) {
                $metadata = config('app.debug') ? [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => array_slice($e->getTrace(), 0, 10),
                ] : [];

                return ApiResponse::error(
                    ErrorCode::SERVER_ERROR,
                    config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                    500,
                    'An unexpected error occurred. Please try again later.',
                    $metadata ?: null
                );
            }
        });
    })->create();
