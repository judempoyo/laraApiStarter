<?php

declare(strict_types=1);
use App\Exports\ApiKeysExport;
use App\Exports\NotificationExport;
use App\Exports\UserPreferenceExport;
use App\Exports\UsersExport;

return [
    /*
    |--------------------------------------------------------------------------
    | Exportable Resources
    |--------------------------------------------------------------------------
    | Map each resource key to its ExportableInterface implementation.
    |
    | User-scoped resources (isAdminOnly = false):
    |   Accessible by any authenticated user for their own data.
    |
    | Admin-only resources (isAdminOnly = true):
    |   Accessible only by administrators. Supports cross-user exports
    |   with optional filters (user_id, date_from, date_to, status, …).
    |
    | To add a new exportable resource:
    |   1. Create a class implementing ExportableInterface
    |   2. Register it here with a unique key
    */
    'resources' => [
        // ── User-scoped (any authenticated user can export their own data) ──
        'user_preferences' => UserPreferenceExport::class,
        'notifications' => NotificationExport::class,

        // ── Admin-only (requires admin role) ────────────────────────────────
        'users' => UsersExport::class,
        'api_keys' => ApiKeysExport::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Storage
    |--------------------------------------------------------------------------
    | Disk and folder used to store generated export files.
    */
    'disk' => env('EXPORT_DISK', 'local'),
    'folder' => 'exports',

    /*
    |--------------------------------------------------------------------------
    | Temporary URL TTL
    |--------------------------------------------------------------------------
    | Minutes the signed download URL remains valid after export completion.
    */
    'url_ttl_minutes' => (int) env('EXPORT_URL_TTL', 60),
];
