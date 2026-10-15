<?php

declare(strict_types=1);

return [
    'version' => env('API_VERSION', 'v1'),
    'auth_guard' => 'sanctum',
    'rate_limits' => [
        'api' => (int) env('RATE_LIMIT_API', 60),
        'auth' => (int) env('RATE_LIMIT_AUTH', 10),
        'login' => (int) env('RATE_LIMIT_LOGIN', 5),
        'register' => (int) env('RATE_LIMIT_REGISTER', 10),
        'password_reset' => (int) env('RATE_LIMIT_PASSWORD_RESET', 3),
    ],
    'pagination' => ['default_per_page' => 15, 'max_per_page' => 100],
    'request' => ['max_size_kb' => (int) env('REQUEST_MAX_SIZE_KB', 0)],
];
