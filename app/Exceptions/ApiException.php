<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ErrorCode;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    public function __construct(
        public readonly ErrorCode $errorCode,
        string $message = '',
        int $statusCode = 400,
        public readonly ?string $userMessage = null,
        public readonly ?array $errors = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($statusCode, $message, $previous);
    }

    /**
     * Create a 400 Bad Request exception.
     */
    public static function badRequest(string $message = 'Bad request.', ErrorCode $errorCode = ErrorCode::VALIDATION_FAILED): self
    {
        return new self(
            errorCode: $errorCode,
            message: $message,
            statusCode: 400,
            userMessage: $message,
        );
    }

    /**
     * Create a 401 Unauthorized exception.
     */
    public static function unauthorized(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::INVALID_CREDENTIALS,
            message: $message ?? 'Invalid credentials.',
            statusCode: 401,
            userMessage: 'The provided credentials are incorrect.',
        );
    }

    /**
     * Create a 403 Forbidden exception.
     */
    public static function forbidden(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::FORBIDDEN,
            message: $message ?? 'Access denied.',
            statusCode: 403,
            userMessage: 'You do not have permission to perform this action.',
        );
    }

    /**
     * Create a 404 Not Found exception.
     */
    public static function notFound(string $resource = 'Resource', ?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::RESOURCE_NOT_FOUND,
            message: $message ?? "{$resource} not found.",
            statusCode: 404,
            userMessage: "The requested {$resource} does not exist.",
        );
    }

    /**
     * Create a 409 Conflict exception.
     */
    public static function conflict(string $message = 'Resource already exists.', ?array $errors = null): self
    {
        return new self(
            errorCode: ErrorCode::CONFLICT,
            message: $message,
            statusCode: 409,
            userMessage: $message,
            errors: $errors,
        );
    }

    /**
     * Create a 410 Gone exception.
     */
    public static function gone(string $resource = 'Resource'): self
    {
        return new self(
            errorCode: ErrorCode::GONE,
            message: "{$resource} has been permanently removed.",
            statusCode: 410,
            userMessage: "The requested {$resource} no longer exists.",
        );
    }

    /**
     * Create a 422 Validation exception.
     */
    public static function validation(string $message, ?array $errors = null): self
    {
        return new self(
            errorCode: ErrorCode::VALIDATION_FAILED,
            message: $message,
            statusCode: 422,
            userMessage: $message,
            errors: $errors,
        );
    }

    /**
     * Create a 422 Unprocessable exception for business logic failures.
     */
    public static function unprocessable(string $message, ErrorCode $errorCode = ErrorCode::UNPROCESSABLE): self
    {
        return new self(
            errorCode: $errorCode,
            message: $message,
            statusCode: 422,
            userMessage: $message,
        );
    }

    /**
     * Create a 429 Too Many Requests exception.
     */
    public static function tooManyRequests(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::TOO_MANY_REQUESTS,
            message: $message ?? 'Too many requests.',
            statusCode: 429,
            userMessage: 'You have exceeded your request limit. Please try again later.',
        );
    }

    /**
     * Create a 500 Server Error exception.
     */
    public static function serverError(?string $message = null, ?\Throwable $previous = null): self
    {
        return new self(
            errorCode: ErrorCode::SERVER_ERROR,
            message: $message ?? 'An unexpected error occurred.',
            statusCode: 500,
            userMessage: 'An unexpected error occurred. Please try again later.',
            previous: $previous,
        );
    }

    /**
     * Create a 503 Service Unavailable exception.
     */
    public static function serviceUnavailable(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::SERVICE_UNAVAILABLE,
            message: $message ?? 'Service temporarily unavailable.',
            statusCode: 503,
            userMessage: 'The service is temporarily unavailable. Please try again later.',
        );
    }
}
