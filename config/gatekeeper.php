<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Gatekeeper allows you to enable or disable specific features such as
    | audit logging, roles, and teams. Disabling features may improve
    | performance and keep your access control system simpler.
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
    | Customize the table names used by Gatekeeper. This is helpful if you're
    | integrating with an existing system or prefer different naming conventions.
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
    | Gatekeeper Dashboard Path
    |--------------------------------------------------------------------------
    |
    | This defines the URI prefix for accessing the Gatekeeper dashboard
    | and internal browser-based tools. This does not affect the API paths.
    |
    */

    'path' => env('GATEKEEPER_PATH', 'gatekeeper'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure the cache prefix and TTL (in seconds) used by Gatekeeper
    | for permissions, roles, and team assignments.
    |
    */

    'cache' => [
        'prefix' => env('GATEKEEPER_CACHE_PREFIX', 'gatekeeper'),
        'ttl' => env('GATEKEEPER_CACHE_TTL', 2 * 60 * 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines model-related configuration for Gatekeeper.
    | It includes the models that can be managed via roles, permissions,
    | and teams.
    |
    */

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | Manageable Models
        |--------------------------------------------------------------------------
        |
        | These models will appear in the Gatekeeper dashboard UI for assigning
        | roles, permissions, and teams. Each entry must include:
        |
        |   - 'label':      A human-readable label for the UI.
        |   - 'class':      The model's fully qualified class name.
        |   - 'searchable': Key-value pairs of column => label for search input placeholder.
        |   - 'display':    Key-value pairs of column => label for table views.
        |
        */

        'manageable' => [

            // 'user' => [
            //     'label' => 'User',
            //     'class' => \App\Models\User::class,
            //     'searchable' => [
            //         'id' => 'ID',
            //         'name' => 'name',
            //         'email' => 'email',
            //     ],
            //     'displayable' => [
            //         'id' => 'ID',
            //         'name' => 'Name',
            //         'email' => 'Email',
            //     ],
            // ],

            'role' => [
                'label' => 'Role',
                'class' => \Gillyware\Gatekeeper\Models\Role::class,
                'searchable' => [
                    'name' => 'name',
                ],
                'displayable' => [
                    'name' => 'Name',
                ],
            ],

            'team' => [
                'label' => 'Team',
                'class' => \Gillyware\Gatekeeper\Models\Team::class,
                'searchable' => [
                    'name' => 'name',
                ],
                'displayable' => [
                    'name' => 'Name',
                ],
            ],

        ],

    ],

];
