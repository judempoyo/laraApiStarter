<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Importable Resources
    |--------------------------------------------------------------------------
    | Map each resource key to its ImportableInterface implementation.
    |
    | User-scoped resources (isAdminOnly = false):
    |   Accessible by any authenticated user for importing their own data.
    |
    | Admin-only resources (isAdminOnly = true):
    |   Accessible only by administrators.
    */
    'resources' => [
        // ── User-scoped (any authenticated user can import their own data) ──
        'user_preferences' => \App\Imports\UserPreferenceImport::class,

        // ── Admin-only (requires admin role) ────────────────────────────────
        'users' => \App\Imports\UsersImport::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Storage Disk
    |--------------------------------------------------------------------------
    | Disk used by MediaService to temporarily store files uploaded for importing.
    */
    'disk' => env('IMPORT_DISK', 'local'),
];
