<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Features
  |--------------------------------------------------------------------------
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
  | If you need to customize table names, you may do so here. This lets you
  | avoid conflicts with other packages or legacy tables.
  |
  */

  'tables' => [
    'permissions'           => 'permissions',
    'roles'                 => 'roles',
    // 'teams'                 => 'teams',
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles'       => 'model_has_roles',
    // 'model_has_teams'       => 'model_has_teams',
  ],

];
