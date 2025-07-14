<?php

namespace Gillyware\Gatekeeper\Facades;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\GatekeeperService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Gillyware\Gatekeeper\Services\GatekeeperService
 *
 * @method static Model|null getActor()
 * @method static string getLifecycleId()
 * @method static GatekeeperService setActor(Model $model)
 * @method static GatekeeperService systemActor()
 * @method static bool permissionExists(string $permissionName)
 * @method static Permission createPermission(string $permissionName)
 * @method static Permission updatePermission(Permission|string $permission, string $permissionName)
 * @method static Permission deactivatePermission(Permission|string $permission)
 * @method static Permission reactivatePermission(Permission|string $permission)
 * @method static bool deletePermission(Permission|string $permission)
 * @method static bool assignPermissionToModel(Model $model, Permission|string $permission)
 * @method static bool assignPermissionsToModel(Model $model, array|Arrayable $permissions)
 * @method static bool revokePermissionFromModel(Model $model, Permission|string $permission)
 * @method static bool revokePermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasPermission(Model $model, Permission|string $permission)
 * @method static bool modelHasAnyPermission(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasAllPermissions(Model $model, array|Arrayable $permissions)
 * @method static ?Permission findPermissionByName(string $permissionName)
 * @method static Collection getAllPermissions()
 * @method static Collection getDirectPermissionsForModel(Model $model)
 * @method static Collection getEffectivePermissionsForModel(Model $model)
 * @method static bool roleExists(string $roleName)
 * @method static Role createRole(string $roleName)
 * @method static Role updateRole(Role|string $role, string $roleName)
 * @method static Role deactivateRole(Role|string $role)
 * @method static Role reactivateRole(Role|string $role)
 * @method static bool deleteRole(Role|string $role)
 * @method static bool assignRoleToModel(Model $model, Role|string $role)
 * @method static bool assignRolesToModel(Model $model, array|Arrayable $roles)
 * @method static bool revokeRoleFromModel(Model $model, Role|string $role)
 * @method static bool revokeRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool modelHasRole(Model $model, Role|string $role)
 * @method static bool modelHasAnyRole(Model $model, array|Arrayable $roles)
 * @method static bool modelHasAllRoles(Model $model, array|Arrayable $roles)
 * @method static ?Role findRoleByName(string $roleName)
 * @method static Collection getAllRoles()
 * @method static Collection getDirectRolesForModel(Model $model)
 * @method static Collection getEffectiveRolesForModel(Model $model)
 * @method static bool teamExists(string $teamName)
 * @method static Team createTeam(string $teamName)
 * @method static Team updateTeam(Team|string $team, string $teamName)
 * @method static Team deactivateTeam(Team|string $team)
 * @method static Team reactivateTeam(Team|string $team)
 * @method static bool deleteTeam(Team|string $team)
 * @method static bool addModelToTeam(Model $model, Team|string $team)
 * @method static bool addModelToTeams(Model $model, array|Arrayable $teams)
 * @method static bool removeModelFromTeam(Model $model, Team|string $team)
 * @method static bool removeModelFromTeams(Model $model, array|Arrayable $teams)
 * @method static bool modelOnTeam(Model $model, Team|string $team)
 * @method static bool modelOnAnyTeam(Model $model, array|Arrayable $teams)
 * @method static bool modelOnAllTeams(Model $model, array|Arrayable $teams)
 * @method static ?Team findTeamByName(string $teamName)
 * @method static Collection getAllTeams()
 * @method static Collection getDirectTeamsForModel(Model $model)
 *
 * @see \Gillyware\Gatekeeper\Services\GatekeeperService
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
