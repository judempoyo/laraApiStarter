<?php

declare(strict_types=1);

return [
    'presets' => [
        'minimal'  => [],
        'standard' => ['rbac', 'two-factor', 'api-keys', 'preferences', 'notifications'],
        'full'     => ['preferences', 'notifications', 'api-keys', 'media', 'export', 'import', 'webhooks', 'two-factor', 'social-login', 'rbac', 'impersonation', 'security-audit'],
    ],
    'default_admin' => [
        'email' => env('DEFAULT_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('DEFAULT_ADMIN_PASSWORD', 'password'),
    ],
    'seeders' => [],
];
