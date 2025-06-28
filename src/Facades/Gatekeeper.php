<?php

namespace Braxey\Gatekeeper\Facades;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\GatekeeperService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Model|null getActor()
 * @method static string getLifecycleId()
 * @method static GatekeeperService setActor(Model $model)
 * @method static GatekeeperService systemActor()
 * @method static Permission createPermission(string $permissionName)
 * @method static bool assignPermissionToModel(Model $model, Permission|string $permission)
 * @method static bool assignPermissionsToModel(Model $model, array|Arrayable $permissions)
 * @method static bool revokePermissionFromModel(Model $model, Permission|string $permission)
 * @method static bool revokePermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasPermission(Model $model, Permission|string $permission)
 * @method static bool modelHasAnyPermission(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasAllPermissions(Model $model, array|Arrayable $permissions)
 * @method static Role createRole(string $roleName)
 * @method static bool assignRoleToModel(Model $model, Role|string $role)
 * @method static bool assignRolesToModel(Model $model, array|Arrayable $roles)
 * @method static bool revokeRoleFromModel(Model $model, Role|string $role)
 * @method static bool revokeRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool modelHasRole(Model $model, Role|string $role)
 * @method static bool modelHasAnyRole(Model $model, array|Arrayable $roles)
 * @method static bool modelHasAllRoles(Model $model, array|Arrayable $roles)
 * @method static Team createTeam(string $teamName)
 * @method static bool addModelToTeam(Model $model, Team|string $team)
 * @method static bool addModelToTeams(Model $model, array|Arrayable $teams)
 * @method static bool removeModelFromTeam(Model $model, Team|string $team)
 * @method static bool removeModelFromTeams(Model $model, array|Arrayable $teams)
 * @method static bool modelOnTeam(Model $model, Team|string $team)
 * @method static bool modelOnAnyTeam(Model $model, array|Arrayable $teams)
 * @method static bool modelOnAllTeams(Model $model, array|Arrayable $teams)
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
