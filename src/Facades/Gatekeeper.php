<?php

namespace Gillyware\Gatekeeper\Facades;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
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
 * @method static PermissionPacket createPermission(string|UnitEnum $permissionName)
 * @method static PermissionPacket updatePermissionName(Permission|PermissionPacket|string|UnitEnum $permission, string|UnitEnum $permissionName)
 * @method static PermissionPacket grantPermissionByDefault(Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static PermissionPacket revokePermissionDefaultGrant(Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static PermissionPacket deactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static PermissionPacket reactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool deletePermission(Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool assignPermissionToModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool assignAllPermissionsToModel(Model $model, array|Arrayable $permissions)
 * @method static bool unassignPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool unassignAllPermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool denyPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool denyAllPermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool undenyPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool undenyAllPermissionsFromModel(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasPermission(Model $model, Permission|PermissionPacket|string|UnitEnum $permission)
 * @method static bool modelHasAnyPermission(Model $model, array|Arrayable $permissions)
 * @method static bool modelHasAllPermissions(Model $model, array|Arrayable $permissions)
 * @method static ?PermissionPacket findPermissionByName(string|UnitEnum $permissionName)
 * @method static Collection getAllPermissions()
 * @method static Collection getDirectPermissionsForModel(Model $model)
 * @method static Collection getEffectivePermissionsForModel(Model $model)
 * @method static Collection getVerbosePermissionsForModel(Model $model)
 * @method static bool roleExists(string|UnitEnum $roleName)
 * @method static RolePacket createRole(string|UnitEnum $roleName)
 * @method static RolePacket updateRoleName(Role|RolePacket|string|UnitEnum $role, string|UnitEnum $roleName)
 * @method static RolePacket grantRoleByDefault(Role|RolePacket|string|UnitEnum $role)
 * @method static RolePacket revokeRoleDefaultGrant(Role|RolePacket|string|UnitEnum $role)
 * @method static RolePacket deactivateRole(Role|RolePacket|string|UnitEnum $role)
 * @method static RolePacket reactivateRole(Role|RolePacket|string|UnitEnum $role)
 * @method static bool deleteRole(Role|RolePacket|string|UnitEnum $role)
 * @method static bool assignRoleToModel(Model $model, Role|RolePacket|string|UnitEnum $role)
 * @method static bool assignAllRolesToModel(Model $model, array|Arrayable $roles)
 * @method static bool unassignRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role)
 * @method static bool unassignAllRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool denyRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role)
 * @method static bool denyAllRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool undenyRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role)
 * @method static bool undenyAllRolesFromModel(Model $model, array|Arrayable $roles)
 * @method static bool modelHasRole(Model $model, Role|RolePacket|string|UnitEnum $role)
 * @method static bool modelHasAnyRole(Model $model, array|Arrayable $roles)
 * @method static bool modelHasAllRoles(Model $model, array|Arrayable $roles)
 * @method static ?RolePacket findRoleByName(string|UnitEnum $roleName)
 * @method static Collection getAllRoles()
 * @method static Collection getDirectRolesForModel(Model $model)
 * @method static Collection getEffectiveRolesForModel(Model $model)
 * @method static Collection getVerboseRolesForModel(Model $model)
 * @method static bool featureExists(string|UnitEnum $featureName)
 * @method static FeaturePacket createFeature(string|UnitEnum $featureName)
 * @method static FeaturePacket updateFeatureName(Feature|FeaturePacket|string|UnitEnum $feature, string|UnitEnum $featureName)
 * @method static FeaturePacket grantFeatureByDefault(Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static FeaturePacket revokeFeatureDefaultGrant(Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static FeaturePacket deactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static FeaturePacket reactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool deleteFeature(Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool assignFeatureForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool assignAllFeaturesForModel(Model $model, array|Arrayable $features)
 * @method static bool unassignFeatureForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool unassignAllFeaturesForModel(Model $model, array|Arrayable $features)
 * @method static bool denyFeatureFromModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool denyAllFeaturesFromModel(Model $model, array|Arrayable $features)
 * @method static bool undenyFeatureFromModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool undenyAllFeaturesFromModel(Model $model, array|Arrayable $features)
 * @method static bool modelHasFeature(Model $model, Feature|FeaturePacket|string|UnitEnum $feature)
 * @method static bool modelHasAnyFeature(Model $model, array|Arrayable $features)
 * @method static bool modelHasAllFeatures(Model $model, array|Arrayable $features)
 * @method static ?FeaturePacket findFeatureByName(string|UnitEnum $featureName)
 * @method static Collection getAllFeatures()
 * @method static Collection getDirectFeaturesForModel(Model $model)
 * @method static Collection getEffectiveFeaturesForModel(Model $model)
 * @method static Collection getVerboseFeaturesForModel(Model $model)
 * @method static bool teamExists(string|UnitEnum $teamName)
 * @method static TeamPacket createTeam(string|UnitEnum $teamName)
 * @method static TeamPacket updateTeamName(Team|TeamPacket|string|UnitEnum $team, string|UnitEnum $teamName)
 * @method static TeamPacket grantTeamByDefault(Team|TeamPacket|string|UnitEnum $team)
 * @method static TeamPacket revokeTeamDefaultGrant(Team|TeamPacket|string|UnitEnum $team)
 * @method static TeamPacket deactivateTeam(Team|TeamPacket|string|UnitEnum $team)
 * @method static TeamPacket reactivateTeam(Team|TeamPacket|string|UnitEnum $team)
 * @method static bool deleteTeam(Team|TeamPacket|string|UnitEnum $team)
 * @method static bool addModelToTeam(Model $model, Team|TeamPacket|string|UnitEnum $team)
 * @method static bool addModelToAllTeams(Model $model, array|Arrayable $teams)
 * @method static bool removeModelFromTeam(Model $model, Team|TeamPacket|string|UnitEnum $team)
 * @method static bool removeModelFromAllTeams(Model $model, array|Arrayable $teams)
 * @method static bool denyTeamFromModel(Model $model, Team|TeamPacket|string|UnitEnum $team)
 * @method static bool denyAllTeamsFromModel(Model $model, array|Arrayable $teams)
 * @method static bool undenyTeamFromModel(Model $model, Team|TeamPacket|string|UnitEnum $team)
 * @method static bool undenyAllTeamsFromModel(Model $model, array|Arrayable $teams)
 * @method static bool modelOnTeam(Model $model, Team|TeamPacket|string|UnitEnum $team)
 * @method static bool modelOnAnyTeam(Model $model, array|Arrayable $teams)
 * @method static bool modelOnAllTeams(Model $model, array|Arrayable $teams)
 * @method static ?TeamPacket findTeamByName(string|UnitEnum $teamName)
 * @method static Collection getAllTeams()
 * @method static Collection getDirectTeamsForModel(Model $model)
 * @method static Collection getEffectiveTeamsForModel(Model $model)
 * @method static Collection getVerboseTeamsForModel(Model $model)
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
