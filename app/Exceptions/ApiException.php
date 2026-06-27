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
}
