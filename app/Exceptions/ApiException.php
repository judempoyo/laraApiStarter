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
    public static function badRequest(string $message = '', ErrorCode $errorCode = ErrorCode::VALIDATION_FAILED): self
    {
        $msg = $message ?: __('api.validation_failed');

        return new self(
            errorCode: $errorCode,
            message: $msg,
            statusCode: 400,
            userMessage: $msg,
        );
    }

    /**
     * Create a 401 Unauthorized exception.
     */
    public static function unauthorized(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::INVALID_CREDENTIALS,
            message: $message ?? __('api.invalid_credentials'),
            statusCode: 401,
            userMessage: __('api.invalid_credentials_message'),
        );
    }

    /**
     * Create a 403 Forbidden exception.
     */
    public static function forbidden(?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::FORBIDDEN,
            message: $message ?? __('api.unauthorized'),
            statusCode: 403,
            userMessage: __('api.unauthorized_message'),
        );
    }

    /**
     * Create a 404 Not Found exception.
     *
     * The resource name is interpolated into the translation string
     * so callers keep passing a plain English noun (e.g. 'User', 'Webhook').
     */
    public static function notFound(string $resource = 'Resource', ?string $message = null): self
    {
        return new self(
            errorCode: ErrorCode::RESOURCE_NOT_FOUND,
            message: $message ?? __('api.not_found', ['resource' => $resource]),
            statusCode: 404,
            userMessage: __('api.not_found_message', ['resource' => $resource]),
        );
    }

    /**
     * Create a 409 Conflict exception.
     */
    public static function conflict(string $message = '', ?array $errors = null): self
    {
        $msg = $message ?: __('api.conflict', ['resource' => 'Resource']);

        return new self(
            errorCode: ErrorCode::CONFLICT,
            message: $msg,
            statusCode: 409,
            userMessage: $msg,
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
            message: __('api.gone', ['resource' => $resource]),
            statusCode: 410,
            userMessage: __('api.gone_message', ['resource' => $resource]),
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
            message: $message ?? __('api.too_many_requests'),
            statusCode: 429,
            userMessage: __('api.too_many_requests_message'),
        );
    }

    /**
     * Create a 500 Server Error exception.
     */
    public static function serverError(?string $message = null, ?\Throwable $previous = null): self
    {
        return new self(
            errorCode: ErrorCode::SERVER_ERROR,
            message: $message ?? __('api.server_error'),
            statusCode: 500,
            userMessage: __('api.server_error_message'),
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
            message: $message ?? __('api.service_unavailable'),
            statusCode: 503,
            userMessage: __('api.service_unavailable_message'),
        );
    }
}
