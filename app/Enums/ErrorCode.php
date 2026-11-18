<?php

declare(strict_types=1);

namespace App\Enums;

enum ErrorCode: string
{
    case UNAUTHENTICATED = 'UNAUTHENTICATED';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case USER_NOT_FOUND = 'USER_NOT_FOUND';
    case EMAIL_NOT_VERIFIED = 'EMAIL_NOT_VERIFIED';
    case EMAIL_ALREADY_VERIFIED = 'EMAIL_ALREADY_VERIFIED';
    case VALIDATION_FAILED = 'VALIDATION_FAILED';
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    case INVALID_VALIDATION_LINK = 'INVALID_VALIDATION_LINK';
    case PASSWORD_MISMATCH = 'PASSWORD_MISMATCH';
    case RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    case ACTION_NOT_ALLOWED = 'ACTION_NOT_ALLOWED';
    case ENUM_NOT_FOUND = 'ENUM_NOT_FOUND';
    case NEED_TO_BE_ADMIN = 'NEED_TO_BE_ADMIN';
    case ACCOUNT_DISABLED = 'ACCOUNT_DISABLED';
    case RESET_PASSWORD_FAILED = 'RESET_PASSWORD_FAILED';

    // Global Errors
    case NOT_FOUND = 'NOT_FOUND';
    case SERVER_ERROR = 'SERVER_ERROR';
    case FORBIDDEN = 'FORBIDDEN';
    case METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case CONFLICT = 'CONFLICT';
    case TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    case GONE = 'GONE';
    case UNPROCESSABLE = 'UNPROCESSABLE';
    case SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    // Two-Factor Authentication
    case TWO_FACTOR_ALREADY_ENABLED = 'TWO_FACTOR_ALREADY_ENABLED';
    case TWO_FACTOR_NOT_ENABLED = 'TWO_FACTOR_NOT_ENABLED';
    case TWO_FACTOR_INVALID_CODE = 'TWO_FACTOR_INVALID_CODE';
    case TWO_FACTOR_NOT_CONFIRMED = 'TWO_FACTOR_NOT_CONFIRMED';
    case TWO_FACTOR_INVALID_RECOVERY_CODE = 'TWO_FACTOR_INVALID_RECOVERY_CODE';
    case TWO_FACTOR_REQUIRED = 'TWO_FACTOR_REQUIRED';

    // API Keys
    case API_KEY_INVALID = 'API_KEY_INVALID';
    case API_KEY_EXPIRED = 'API_KEY_EXPIRED';
    case API_KEY_REVOKED = 'API_KEY_REVOKED';

    // Impersonation
    case IMPERSONATION_NOT_ALLOWED = 'IMPERSONATION_NOT_ALLOWED';
    case IMPERSONATION_SELF = 'IMPERSONATION_SELF';
    case SUSPICIOUS_REQUEST = 'SUSPICIOUS_REQUEST';
    case REQUEST_TOO_LARGE = 'REQUEST_TOO_LARGE';

    // Webhooks
    case WEBHOOK_NOT_FOUND = 'WEBHOOK_NOT_FOUND';
    case WEBHOOK_DELIVERY_FAILED = 'WEBHOOK_DELIVERY_FAILED';

    // Media
    case MEDIA_NOT_FOUND = 'MEDIA_NOT_FOUND';
    case MEDIA_UPLOAD_FAILED = 'MEDIA_UPLOAD_FAILED';

    // Exports
    case EXPORT_NOT_FOUND = 'EXPORT_NOT_FOUND';
    case EXPORT_FAILED = 'EXPORT_FAILED';

    /**
     * Map each error code strictly to its standard HTTP status code.
     */
    public function httpStatus(): int
    {
        return match ($this) {
            self::UNAUTHENTICATED,
            self::INVALID_CREDENTIALS,
            self::API_KEY_INVALID,
            self::API_KEY_EXPIRED,
            self::API_KEY_REVOKED => 401,

            self::UNAUTHORIZED,
            self::FORBIDDEN,
            self::NEED_TO_BE_ADMIN,
            self::ACCOUNT_DISABLED,
            self::EMAIL_NOT_VERIFIED,
            self::TWO_FACTOR_REQUIRED,
            self::ACTION_NOT_ALLOWED,
            self::IMPERSONATION_NOT_ALLOWED,
            self::IMPERSONATION_SELF => 403,

            self::USER_NOT_FOUND,
            self::RESOURCE_NOT_FOUND,
            self::NOT_FOUND,
            self::ENUM_NOT_FOUND,
            self::WEBHOOK_NOT_FOUND,
            self::MEDIA_NOT_FOUND,
            self::EXPORT_NOT_FOUND => 404,

            self::METHOD_NOT_ALLOWED => 405,

            self::CONFLICT,
            self::EMAIL_ALREADY_VERIFIED,
            self::TWO_FACTOR_ALREADY_ENABLED => 409,

            self::GONE => 410,

            self::REQUEST_TOO_LARGE => 413,

            self::VALIDATION_FAILED,
            self::PASSWORD_MISMATCH,
            self::INVALID_VALIDATION_LINK,
            self::RESET_PASSWORD_FAILED,
            self::TWO_FACTOR_NOT_ENABLED,
            self::TWO_FACTOR_INVALID_CODE,
            self::TWO_FACTOR_NOT_CONFIRMED,
            self::TWO_FACTOR_INVALID_RECOVERY_CODE,
            self::SUSPICIOUS_REQUEST,
            self::UNPROCESSABLE => 422,

            self::RATE_LIMIT_EXCEEDED,
            self::TOO_MANY_REQUESTS => 429,

            self::SERVICE_UNAVAILABLE => 503,

            self::SERVER_ERROR,
            self::WEBHOOK_DELIVERY_FAILED,
            self::MEDIA_UPLOAD_FAILED,
            self::EXPORT_FAILED => 500,
        };
    }

    /**
     * Group error codes into logical categories for developers and API clients.
     */
    public function category(): string
    {
        return match ($this) {
            self::UNAUTHENTICATED,
            self::UNAUTHORIZED,
            self::FORBIDDEN,
            self::INVALID_CREDENTIALS,
            self::NEED_TO_BE_ADMIN,
            self::ACCOUNT_DISABLED,
            self::EMAIL_NOT_VERIFIED,
            self::EMAIL_ALREADY_VERIFIED,
            self::INVALID_VALIDATION_LINK,
            self::PASSWORD_MISMATCH,
            self::RESET_PASSWORD_FAILED => 'Authentication & Access',

            self::TWO_FACTOR_ALREADY_ENABLED,
            self::TWO_FACTOR_NOT_ENABLED,
            self::TWO_FACTOR_INVALID_CODE,
            self::TWO_FACTOR_NOT_CONFIRMED,
            self::TWO_FACTOR_INVALID_RECOVERY_CODE,
            self::TWO_FACTOR_REQUIRED => 'Two-Factor Authentication (2FA)',

            self::API_KEY_INVALID,
            self::API_KEY_EXPIRED,
            self::API_KEY_REVOKED => 'API Keys',

            self::RATE_LIMIT_EXCEEDED,
            self::TOO_MANY_REQUESTS => 'Rate Limiting & Quotas',

            self::SUSPICIOUS_REQUEST,
            self::REQUEST_TOO_LARGE,
            self::IMPERSONATION_NOT_ALLOWED,
            self::IMPERSONATION_SELF => 'Security & Guard',

            self::VALIDATION_FAILED,
            self::UNPROCESSABLE => 'Validation & Request',

            self::USER_NOT_FOUND,
            self::RESOURCE_NOT_FOUND,
            self::NOT_FOUND,
            self::ENUM_NOT_FOUND,
            self::CONFLICT,
            self::GONE,
            self::ACTION_NOT_ALLOWED,
            self::METHOD_NOT_ALLOWED => 'Resources & Routing',

            self::WEBHOOK_NOT_FOUND,
            self::WEBHOOK_DELIVERY_FAILED => 'Webhooks',

            self::MEDIA_NOT_FOUND,
            self::MEDIA_UPLOAD_FAILED => 'Media & Storage',

            self::EXPORT_NOT_FOUND,
            self::EXPORT_FAILED => 'Data Exports',

            self::SERVER_ERROR,
            self::SERVICE_UNAVAILABLE => 'Server & System',
        };
    }

    /**
     * Get the localized human-readable title.
     */
    public function title(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        $key = "errors.codes.{$this->value}.title";
        $translated = __($key, [], $loc);

        if ($translated !== $key && is_string($translated)) {
            return $translated;
        }

        return \Illuminate\Support\Str::headline(str_replace('_', ' ', strtolower($this->value)));
    }

    /**
     * Get the localized detailed description.
     */
    public function description(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        $key = "errors.codes.{$this->value}.description";
        $translated = __($key, [], $loc);

        if ($translated !== $key && is_string($translated)) {
            return $translated;
        }

        return "Application error code: {$this->value}.";
    }

    /**
     * Build an RFC 7807 compliant problem details payload for documentation.
     *
     * @return array<string, mixed>
     */
    public function examplePayload(?string $locale = null): array
    {
        return [
            'type' => url("/docs/errors#{$this->value}"),
            'title' => $this->title($locale),
            'status' => $this->httpStatus(),
            'detail' => $this->description($locale),
            'instance' => '/api/v1/...',
            'code' => $this->httpStatus(),
            'success' => false,
            'error' => [
                'code' => $this->value,
                'message' => $this->description($locale),
            ],
            'message' => $this->description($locale),
            'errors' => null,
            'data' => null,
        ];
    }
}
