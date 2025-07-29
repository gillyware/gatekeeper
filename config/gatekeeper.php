<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gatekeeper Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Gatekeeper will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('GATEKEEPER_PATH', 'gatekeeper'),

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | Specifies which timezone to use for displaying datetimes on the dashboard.
    |
    */

    'timezone' => env('GATEKEEPER_TIMEZONE', config('app.timezone', 'UTC')),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Determines which Gatekeeper features are enabled.
    |
    */

    'features' => [
        'audit' => [
            'enabled' => true,
        ],
        'roles' => [
            'enabled' => true,
        ],
        'features' => [
            'enabled' => false,
        ],
        'teams' => [
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | Defines the database table names used by Gatekeeper. These may be
    | customized to align with existing schemas or naming patterns.
    |
    */

    'tables' => [
        'permissions' => 'permissions',
        'roles' => 'roles',
        'features' => 'features',
        'teams' => 'teams',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'model_has_features' => 'model_has_features',
        'model_has_teams' => 'model_has_teams',
        'audit_log' => 'gatekeeper_audit_log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure the cache prefix and TTL (in seconds) used for caching
    | entities (permissions, roles, teams) and entity assignments.
    |
    */

    'cache' => [
        'enabled' => env('GATEKEEPER_CACHE_ENABLED', true),
        'prefix' => env('GATEKEEPER_CACHE_PREFIX', 'gatekeeper'),
        'ttl' => env('GATEKEEPER_CACHE_TTL', 2 * 60 * 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Defines the models available to Gatekeeper. Each entry contains the
    | attributes to identify, search, and display the model using Gatekeeper.
    |
    */

    'models' => [

        'manageable' => [

            // 'user' => [
            //     'label' => 'User',
            //     'class' => \App\Models\User::class,
            //     'searchable' => [
            //         ['column' => 'id', 'label' => 'ID'],
            //         ['column' => 'name', 'label' => 'name'],
            //         ['column' => 'email', 'label' => 'email'],
            //     ],
            //     'displayable' => [
            //         ['column' => 'id', 'label' => 'ID', 'cli_width' => 10],
            //         ['column' => 'name', 'label' => 'Name', 'cli_width' => 25],
            //         ['column' => 'email', 'label' => 'Email', 'cli_width' => 35],
            //     ],
            // ],

            'role' => [
                'label' => 'Role',
                'class' => \Gillyware\Gatekeeper\Models\Role::class,
                'searchable' => [
                    ['column' => 'name', 'label' => 'name'],
                ],
                'displayable' => [
                    ['column' => 'name', 'label' => 'Name', 'cli_width' => 20],
                    ['column' => 'is_active', 'label' => 'Active', 'cli_width' => 15],
                ],
            ],

            'feature' => [
                'label' => 'Feature',
                'class' => \Gillyware\Gatekeeper\Models\Feature::class,
                'searchable' => [
                    ['column' => 'name', 'label' => 'name'],
                ],
                'displayable' => [
                    ['column' => 'name', 'label' => 'Name', 'cli_width' => 20],
                    ['column' => 'grant_by_default', 'label' => 'On By Default', 'cli_width' => 20],
                    ['column' => 'is_active', 'label' => 'Active', 'cli_width' => 15],
                ],
            ],

            'team' => [
                'label' => 'Team',
                'class' => \Gillyware\Gatekeeper\Models\Team::class,
                'searchable' => [
                    ['column' => 'name', 'label' => 'name'],
                ],
                'displayable' => [
                    ['column' => 'name', 'label' => 'Name', 'cli_width' => 20],
                    ['column' => 'is_active', 'label' => 'Active', 'cli_width' => 15],
                ],
            ],

        ],

    ],

];
