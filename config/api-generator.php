<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Generator Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | Define the root namespaces for each generated component.
    | These are used by the make:* commands to resolve proper namespaces.
    |
    */

    'namespaces' => [
        'dto'        => 'App\\DTOs',
        'action'     => 'App\\Actions',
        'request'    => 'App\\Http\\Requests',
        'resource'   => 'App\\Http\\Resources',
        'controller' => 'App\\Http\\Controllers\\Api\\v1',
        'model'      => 'App\\Models',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generator Paths
    |--------------------------------------------------------------------------
    |
    | Base paths (relative to app/) for each generated component.
    |
    */

    'paths' => [
        'dto'        => 'DTOs',
        'action'     => 'Actions',
        'request'    => 'Http/Requests',
        'resource'   => 'Http/Resources',
        'controller' => 'Http/Controllers/Api/v1',
        'stub'       => 'stubs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fields to Exclude from Introspection
    |--------------------------------------------------------------------------
    |
    | These fields will be excluded when introspecting models to generate
    | DTO properties, request rules, and resource fields.
    |
    */

    'excluded_fields' => [
        'id',
        'password',
        'remember_token',
        'email_verified_at',
        'password_updated_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Timestamp Fields
    |--------------------------------------------------------------------------
    |
    | These fields will always be included in generated resources
    | (even though they are excluded from DTOs and requests).
    |
    */

    'resource_timestamp_fields' => [
        'created_at',
        'updated_at',
    ],

];
