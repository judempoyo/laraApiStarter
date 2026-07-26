<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    | The current version of the API. Used in route prefixes and responses.
    */
    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Driver
    |--------------------------------------------------------------------------
    | Supported: "sanctum", "passport"
    |
    | This controls which token service is bound in the container.
    | When using "passport", you must also swap the HasApiTokens trait
    | in your User model from Laravel\Sanctum\HasApiTokens to
    | Laravel\Passport\HasApiTokens, and run `php artisan passport:install`.
    */
    'auth_driver' => env('AUTH_DRIVER', 'sanctum'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    | The guard used for authenticated API routes.
    |   - "sanctum"  for Sanctum (default)
    |   - "api"      for Passport
    */
    'auth_guard' => env('AUTH_GUARD', 'sanctum'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Number of requests allowed per minute for each limiter.
    */
    'rate_limits' => [
        'api'            => (int) env('RATE_LIMIT_API', 60),
        'auth'           => (int) env('RATE_LIMIT_AUTH', 10),
        'login'          => (int) env('RATE_LIMIT_LOGIN', 5),
        'register'       => (int) env('RATE_LIMIT_REGISTER', 10),
        'password_reset' => (int) env('RATE_LIMIT_PASSWORD_RESET', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => (int) env('PAGINATION_PER_PAGE', 15),
        'max_per_page'     => (int) env('PAGINATION_MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Constraints
    |--------------------------------------------------------------------------
    */
    'request' => [
        'max_size_kb' => (int) env('REQUEST_MAX_SIZE_KB', 10240),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers — Content Security Policy
    |--------------------------------------------------------------------------
    | Directives used by SecurityHeadersMiddleware.
    */
    'security' => [
        'csp' => [
            "default-src 'self'",
            "script-src 'self' https://unpkg.com 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https://unpkg.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
        ],

        /*
        | Patterns that trigger the SuspiciousRequestMiddleware.
        | Requests matching any of these will be rejected with a 400.
        */
        'suspicious_patterns' => [
            '../',
            '..\\',
            '<script',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror=',
            'SELECT ',
            'UNION ',
            'DROP TABLE',
            'INSERT INTO',
            '/etc/passwd',
            '/proc/self',
        ],
    ],

];
