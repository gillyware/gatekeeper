<?php

namespace Braxey\Gatekeeper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static Permission createPermission(string $name)
 * @method static bool assignPermissionToModel(Model $model, string $permissionName)
 * @method static bool assignPermissionsToModel(Model $model, array|Arrayable $permissionNames)
 * @method static bool revokePermissionFromModel(Model $model, string $permissionName)
 * @method static bool revokePermissionsFromModel(Model $model, array|Arrayable $permissionNames)
 * @method static bool modelHasPermission(Model $model, string $permissionName)
 * @method static bool modelHasAnyPermission(Model $model, array|Arrayable $permissionNames)
 * @method static bool modelHasAllPermissions(Model $model, array|Arrayable $permissionNames)
 * @method static Role createRole(string $name)
 * @method static bool assignRoleToModel(Model $model, string $roleName)
 * @method static bool assignRolesToModel(Model $model, array|Arrayable $roleNames)
 * @method static bool revokeRoleFromModel(Model $model, string $roleName)
 * @method static bool revokeRolesFromModel(Model $model, array|Arrayable $roleNames)
 * @method static bool modelHasRole(Model $model, string $roleName)
 * @method static bool modelHasAnyRole(Model $model, array|Arrayable $roleNames)
 * @method static bool modelHasAllRoles(Model $model, array|Arrayable $roleNames)
 * @method static Team createTeam(string $name)
 * @method static bool addModelToTeam(Model $model, string $teamName)
 * @method static bool addModelToTeams(Model $model, array|Arrayable $teamNames)
 * @method static bool removeModelFromTeam(Model $model, string $teamName)
 * @method static bool removeModelFromTeams(Model $model, array|Arrayable $teamNames)
 * @method static bool modelOnTeam(Model $model, string $teamName)
 * @method static bool modelOnAnyTeam(Model $model, array|Arrayable $teamNames)
 * @method static bool modelOnAllTeams(Model $model, array|Arrayable $teamNames)
 *
 * @see \Braxey\Gatekeeper\Services\GatekeeperService
 */
class Gatekeeper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gatekeeper';
    }
}
