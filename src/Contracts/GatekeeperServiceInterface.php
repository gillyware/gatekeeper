<?php

namespace Gillyware\Gatekeeper\Contracts;

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
use UnitEnum;

interface GatekeeperServiceInterface
{
    /**
     * Get the currently acting as model.
     */
    public function getActor(): ?Model;

    /**
     * Get the lifecycle ID for the current request or CLI execution.
     */
    public function getLifecycleId(): string;

    /**
     * Set the acting as model.
     */
    public function setActor(Model $model): GatekeeperService;

    /**
     * Set the actor to a system actor.
     */
    public function systemActor(): GatekeeperService;

    /**
     * Manage Gatekeeper for a specific model.
     */
    public function for(Model $model): GatekeeperForModelService;

    /**
     * Check if a permission exists.
     */
    public function permissionExists(string|UnitEnum $permissionName): bool;

    /**
     * Create a new permission.
     */
    public function createPermission(string|UnitEnum $permissionName): PermissionPacket;

    /**
     * Update an existing permission.
     */
    public function updatePermission(Permission|PermissionPacket|string|UnitEnum $permission, string|UnitEnum $permissionName): PermissionPacket;

    /**
     * Deactivate a permission.
     */
    public function deactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket;

    /**
     * Reactivate a permission.
     */
    public function reactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket;

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Assign a permission to a model.
     */
    public function assignPermissionToModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Assign multiple permissions to a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function assignAllPermissionsToModel(Model $model, array|Arrayable $permissions): bool;

    /**
     * Revoke a permission from a model.
     */
    public function revokePermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Revoke multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function revokeAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool;

    /**
     * Check if a model has the given permission.
     */
    public function modelHasPermission(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Check if a model has any of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool;

    /**
     * Check if a model has all of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool;

    /**
     * Find a permission by its name.
     */
    public function findPermissionByName(string|UnitEnum $permissionName): ?PermissionPacket;

    /**
     * Get all permissions.
     *
     * @return Collection<PermissionPacket>
     */
    public function getAllPermissions(): Collection;

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<PermissionPacket>
     */
    public function getDirectPermissionsForModel(Model $model): Collection;

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<PermissionPacket>
     */
    public function getEffectivePermissionsForModel(Model $model): Collection;

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerbosePermissionsForModel(Model $model): Collection;

    /**
     * Check if a role exists.
     */
    public function roleExists(string|UnitEnum $roleName): bool;

    /**
     * Create a new role.
     */
    public function createRole(string|UnitEnum $roleName): RolePacket;

    /**
     * Update an existing role.
     */
    public function updateRole(Role|RolePacket|string|UnitEnum $role, string|UnitEnum $roleName): RolePacket;

    /**
     * Deactivate a role.
     */
    public function deactivateRole(Role|RolePacket|string|UnitEnum $role): RolePacket;

    /**
     * Reactivate a role.
     */
    public function reactivateRole(Role|RolePacket|string|UnitEnum $role): RolePacket;

    /**
     * Delete a role.
     */
    public function deleteRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Assign a role to a model.
     */
    public function assignRoleToModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Assign multiple roles to a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function assignAllRolesToModel(Model $model, array|Arrayable $roles): bool;

    /**
     * Revoke a role from a model.
     */
    public function revokeRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Revoke multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function revokeAllRolesFromModel(Model $model, array|Arrayable $roles): bool;

    /**
     * Check if a model has the given role.
     */
    public function modelHasRole(Model $model, Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Check if a model has any of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool;

    /**
     * Check if a model has all of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool;

    /**
     * Find a role by its name.
     */
    public function findRoleByName(string|UnitEnum $roleName): ?RolePacket;

    /**
     * Get all roles.
     *
     * @return Collection<RolePacket>
     */
    public function getAllRoles(): Collection;

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<RolePacket>
     */
    public function getDirectRolesForModel(Model $model): Collection;

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<RolePacket>
     */
    public function getEffectiveRolesForModel(Model $model): Collection;

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseRolesForModel(Model $model): Collection;

    /**
     * Check if a feature exists.
     */
    public function featureExists(string|UnitEnum $featureName): bool;

    /**
     * Create a new feature.
     */
    public function createFeature(string|UnitEnum $featureName): FeaturePacket;

    /**
     * Update an existing feature.
     */
    public function updateFeature(Feature|FeaturePacket|string|UnitEnum $feature, string|UnitEnum $featureName): FeaturePacket;

    /**
     * Turn feature off by default.
     */
    public function turnFeatureOffByDefault(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket;

    /**
     * Turn feature on by default.
     */
    public function turnFeatureOnByDefault(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket;

    /**
     * Deactivate a feature.
     */
    public function deactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket;

    /**
     * Reactivate a feature.
     */
    public function reactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket;

    /**
     * Delete a feature.
     */
    public function deleteFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Turn a feature on for a model.
     */
    public function turnFeatureOnForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Turn multiple features on for a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function turnAllFeaturesOnForModel(Model $model, array|Arrayable $features): bool;

    /**
     * Turn a feature off for a model.
     */
    public function turnFeatureOffForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Turn multiple features off for a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function turnAllFeaturesOffForModel(Model $model, array|Arrayable $features): bool;

    /**
     * Check if a model has the given feature.
     */
    public function modelHasFeature(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Check if a model has any of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAnyFeature(Model $model, array|Arrayable $features): bool;

    /**
     * Check if a model has all of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAllFeatures(Model $model, array|Arrayable $features): bool;

    /**
     * Find a feature by its name.
     */
    public function findFeatureByName(string|UnitEnum $featureName): ?FeaturePacket;

    /**
     * Get all features.
     *
     * @return Collection<FeaturePacket>
     */
    public function getAllFeatures(): Collection;

    /**
     * Get all features assigned directly to a model.
     *
     * @return Collection<FeaturePacket>
     */
    public function getDirectFeaturesForModel(Model $model): Collection;

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<FeaturePacket>
     */
    public function getEffectiveFeaturesForModel(Model $model): Collection;

    /**
     * Get all effective features for the given model with the feature source(s).
     */
    public function getVerboseFeaturesForModel(Model $model): Collection;

    /**
     * Check if a team exists.
     */
    public function teamExists(string|UnitEnum $teamName): bool;

    /**
     * Create a new team.
     */
    public function createTeam(string|UnitEnum $teamName): TeamPacket;

    /**
     * Update an existing team.
     */
    public function updateTeam(Team|TeamPacket|string|UnitEnum $team, string|UnitEnum $teamName): TeamPacket;

    /**
     * Deactivate a team.
     */
    public function deactivateTeam(Team|TeamPacket|string|UnitEnum $team): TeamPacket;

    /**
     * Reactivate a team.
     */
    public function reactivateTeam(Team|TeamPacket|string|UnitEnum $team): TeamPacket;

    /**
     * Delete a team.
     */
    public function deleteTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Add a model to a team.
     */
    public function addModelToTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Add a model to multiple teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function addModelToAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Remove a model from a team.
     */
    public function removeModelFromTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Remove a model from multiple teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function removeModelFromAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Check if a model is on a given team.
     */
    public function modelOnTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Check if a model is on any of the specified teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool;

    /**
     * Check if a model is on all of the specified teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Find a team by its name.
     */
    public function findTeamByName(string|UnitEnum $teamName): ?TeamPacket;

    /**
     * Get all teams.
     *
     * @return Collection<TeamPacket>
     */
    public function getAllTeams(): Collection;

    /**
     * Get all teams assigned to a model.
     *
     * @return Collection<TeamPacket>
     */
    public function getTeamsForModel(Model $model): Collection;
}
