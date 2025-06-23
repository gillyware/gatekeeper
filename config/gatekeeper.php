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
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'ttl' => env('GATEKEEPER_CACHE_TTL', 24 * 60 * 60), // 24 hours
    ],

];
