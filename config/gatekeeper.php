<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Gatekeeper allows you to enable or disable specific features such as
    | roles or teams. Disabling features may improve performance and keep
    | your permission system as simple as possible for your use case.
    |
    */

    'features' => [
        'audit' => true,
        'roles' => true,
        'teams' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | You may customize the database table names used by Gatekeeper. This
    | is useful if you're integrating Gatekeeper into an existing system
    | or if you simply prefer different naming conventions for tables.
    |
    */

    'tables' => [
        'permissions' => 'permissions',
        'roles' => 'roles',
        'teams' => 'teams',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'model_has_teams' => 'model_has_teams',
        'audit_logs' => 'gatekeeper_audit_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'prefix' => env('GATEKEEPER_CACHE_PREFIX', 'gatekeeper'),
        'ttl' => env('GATEKEEPER_CACHE_TTL', 2 * 60 * 60),
    ],

];
