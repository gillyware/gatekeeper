<?php

namespace Gillyware\Gatekeeper\Facades;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Services\GatekeeperService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use UnitEnum;

/**
 * @mixin \Gillyware\Gatekeeper\Services\GatekeeperService
 *
 * @method static Model|null getActor()
 * @method static string getLifecycleId()
 * @method static GatekeeperService setActor(Model $model)
 * @method static GatekeeperService systemActor()
 * @method static GatekeeperForModelService for(Model $model)
 * @method static bool permissionExists(string|UnitEnum $permissionName)
 * @method static Permission createPermission(string|UnitEnum $permissionName)
 * @method static Permission updatePermission(Permission|string|UnitEnum $permission, string|UnitEnum $permissionName)
 * @method static Permission deactivatePermission(Permission|string|UnitEnum $permission)
 * @method static Permission reactivatePermission(Permission|string|UnitEnum $permission)
 * @method static bool deletePermission(Permission|string|UnitEnum $permission)
 * @method static bool assignPermissionToModel(Model $model, Permission|string|UnitEnum $permission)
 * @method static bool assignAllPermissionsToModel(Model $model, array|Arrayable $permissions)
 * @method static bool revokePermissionFromModel(Model $model, Permission|string|UnitEnum $permission)
 * @method static bool revokeAllPermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasPermission(Model $model, Permission|string|UnitEnum $permission)
 * @method static bool modelHasAnyPermission(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasAllPermissions(Model $model, array|Arrayable $permissions)
 * @method static ?Permission findPermissionByName(string|UnitEnum $permissionName)
 * @method static Collection getAllPermissions()
 * @method static Collection getDirectPermissionsForModel(Model $model)
 * @method static Collection getEffectivePermissionsForModel(Model $model)
 * @method static Collection getVerbosePermissionsForModel(Model $model)
 * @method static bool roleExists(string|UnitEnum $roleName)
 * @method static Role createRole(string|UnitEnum $roleName)
 * @method static Role updateRole(Role|string|UnitEnum $role, string|UnitEnum $roleName)
 * @method static Role deactivateRole(Role|string|UnitEnum $role)
 * @method static Role reactivateRole(Role|string|UnitEnum $role)
 * @method static bool deleteRole(Role|string|UnitEnum $role)
 * @method static bool assignRoleToModel(Model $model, Role|string|UnitEnum $role)
 * @method static bool assignAllRolesToModel(Model $model, array|Arrayable $roles)
 * @method static bool revokeRoleFromModel(Model $model, Role|string|UnitEnum $role)
 * @method static bool revokeAllRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool modelHasRole(Model $model, Role|string|UnitEnum $role)
 * @method static bool modelHasAnyRole(Model $model, array|Arrayable $roles)
 * @method static bool modelHasAllRoles(Model $model, array|Arrayable $roles)
 * @method static ?Role findRoleByName(string|UnitEnum $roleName)
 * @method static Collection getAllRoles()
 * @method static Collection getDirectRolesForModel(Model $model)
 * @method static Collection getEffectiveRolesForModel(Model $model)
 * @method static bool teamExists(string|UnitEnum $teamName)
 * @method static Team createTeam(string|UnitEnum $teamName)
 * @method static Team updateTeam(Team|string|UnitEnum $team, string|UnitEnum $teamName)
 * @method static Team deactivateTeam(Team|string|UnitEnum $team)
 * @method static Team reactivateTeam(Team|string|UnitEnum $team)
 * @method static bool deleteTeam(Team|string|UnitEnum $team)
 * @method static bool addModelToTeam(Model $model, Team|string|UnitEnum $team)
 * @method static bool addModelToAllTeams(Model $model, array|Arrayable $teams)
 * @method static bool removeModelFromTeam(Model $model, Team|string|UnitEnum $team)
 * @method static bool removeModelFromAllTeams(Model $model, array|Arrayable $teams)
 * @method static bool modelOnTeam(Model $model, Team|string|UnitEnum $team)
 * @method static bool modelOnAnyTeam(Model $model, array|Arrayable $teams)
 * @method static bool modelOnAllTeams(Model $model, array|Arrayable $teams)
 * @method static ?Team findTeamByName(string|UnitEnum $teamName)
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
