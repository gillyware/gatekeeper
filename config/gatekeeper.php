<?php

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;

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

    'path' => env('GATEKEEPER_PATH', GatekeeperConfigDefault::PATH),

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
            'enabled' => GatekeeperConfigDefault::FEATURES_AUDIT_ENABLED,
        ],
        'roles' => [
            'enabled' => GatekeeperConfigDefault::FEATURES_ROLES_ENABLED,
        ],
        'teams' => [
            'enabled' => GatekeeperConfigDefault::FEATURES_TEAMS_ENABLED,
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
        'permissions' => GatekeeperConfigDefault::TABLES_PERMISSIONS,
        'roles' => GatekeeperConfigDefault::TABLES_ROLES,
        'teams' => GatekeeperConfigDefault::TABLES_TEAMS,
        'model_has_permissions' => GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS,
        'model_has_roles' => GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES,
        'model_has_teams' => GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS,
        'audit_logs' => GatekeeperConfigDefault::TABLES_AUDIT_LOGS,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure the cache prefix and TTL (in seconds) used for caching
    | entities (permissions, roles, teams) and entity assignemnts.
    |
    */

    'cache' => [
        'enabled' => env('GATEKEEPER_CACHE_ENABLED', GatekeeperConfigDefault::CACHE_ENABLED),
        'prefix' => env('GATEKEEPER_CACHE_PREFIX', GatekeeperConfigDefault::CACHE_PREFIX),
        'ttl' => env('GATEKEEPER_CACHE_TTL', GatekeeperConfigDefault::CACHE_TTL),
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
                ],
            ],

        ],

    ],

];
