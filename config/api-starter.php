<?php

declare(strict_types=1);

return [
    'presets' => [
        'minimal' => [],
        'standard' => ['rbac', 'two-factor', 'api-keys', 'preferences', 'notifications'],
        'full' => ['preferences', 'notifications', 'api-keys', 'media', 'export', 'import', 'webhooks', 'two-factor', 'social-login', 'rbac', 'impersonation', 'security-audit'],
    ],
    'seeders' => [],
];
