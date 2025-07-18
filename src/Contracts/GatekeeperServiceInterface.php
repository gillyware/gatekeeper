<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
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
    public function createPermission(string|UnitEnum $permissionName): Permission;

    /**
     * Update an existing permission.
     */
    public function updatePermission(Permission|string|UnitEnum $permission, string|UnitEnum $permissionName): Permission;

    /**
     * Deactivate a permission.
     */
    public function deactivatePermission(Permission|string|UnitEnum $permission): Permission;

    /**
     * Reactivate a permission.
     */
    public function reactivatePermission(Permission|string|UnitEnum $permission): Permission;

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission|string|UnitEnum $permission): bool;

    /**
     * Assign a permission to a model.
     */
    public function assignPermissionToModel(Model $model, Permission|string|UnitEnum $permission): bool;

    /**
     * Assign multiple permissions to a model.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function assignAllPermissionsToModel(Model $model, array|Arrayable $permissions): bool;

    /**
     * Revoke a permission from a model.
     */
    public function revokePermissionFromModel(Model $model, Permission|string|UnitEnum $permission): bool;

    /**
     * Revoke multiple permissions from a model.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function revokeAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool;

    /**
     * Check if a model has the given permission.
     */
    public function modelHasPermission(Model $model, Permission|string|UnitEnum $permission): bool;

    /**
     * Check if a model has any of the given permissions.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool;

    /**
     * Check if a model has all of the given permissions.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool;

    /**
     * Find a permission by its name.
     */
    public function findPermissionByName(string|UnitEnum $permissionName): ?Permission;

    /**
     * Get all permissions.
     *
     * @return Collection<Permission>
     */
    public function getAllPermissions(): Collection;

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<Permission>
     */
    public function getDirectPermissionsForModel(Model $model): Collection;

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<Permission>
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
    public function createRole(string|UnitEnum $roleName): Role;

    /**
     * Update an existing role.
     */
    public function updateRole(Role|string|UnitEnum $role, string|UnitEnum $roleName): Role;

    /**
     * Deactivate a role.
     */
    public function deactivateRole(Role|string|UnitEnum $role): Role;

    /**
     * Reactivate a role.
     */
    public function reactivateRole(Role|string|UnitEnum $role): Role;

    /**
     * Delete a role.
     */
    public function deleteRole(Role|string|UnitEnum $role): bool;

    /**
     * Assign a role to a model.
     */
    public function assignRoleToModel(Model $model, Role|string|UnitEnum $role): bool;

    /**
     * Assign multiple roles to a model.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function assignAllRolesToModel(Model $model, array|Arrayable $roles): bool;

    /**
     * Revoke a role from a model.
     */
    public function revokeRoleFromModel(Model $model, Role|string|UnitEnum $role): bool;

    /**
     * Revoke multiple roles from a model.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function revokeAllRolesFromModel(Model $model, array|Arrayable $roles): bool;

    /**
     * Check if a model has the given role.
     */
    public function modelHasRole(Model $model, Role|string|UnitEnum $role): bool;

    /**
     * Check if a model has any of the given roles.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool;

    /**
     * Check if a model has all of the given roles.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool;

    /**
     * Find a role by its name.
     */
    public function findRoleByName(string|UnitEnum $roleName): ?Role;

    /**
     * Get all roles.
     *
     * @return Collection<Role>
     */
    public function getAllRoles(): Collection;

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<Role>
     */
    public function getDirectRolesForModel(Model $model): Collection;

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<Role>
     */
    public function getEffectiveRolesForModel(Model $model): Collection;

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseRolesForModel(Model $model): Collection;

    /**
     * Check if a team exists.
     */
    public function teamExists(string|UnitEnum $teamName): bool;

    /**
     * Create a new team.
     */
    public function createTeam(string|UnitEnum $teamName): Team;

    /**
     * Update an existing team.
     */
    public function updateTeam(Team|string|UnitEnum $team, string|UnitEnum $teamName): Team;

    /**
     * Deactivate a team.
     */
    public function deactivateTeam(Team|string|UnitEnum $team): Team;

    /**
     * Reactivate a team.
     */
    public function reactivateTeam(Team|string|UnitEnum $team): Team;

    /**
     * Delete a team.
     */
    public function deleteTeam(Team|string|UnitEnum $team): bool;

    /**
     * Add a model to a team.
     */
    public function addModelToTeam(Model $model, Team|string|UnitEnum $team): bool;

    /**
     * Add a model to multiple teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function addModelToAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Remove a model from a team.
     */
    public function removeModelFromTeam(Model $model, Team|string|UnitEnum $team): bool;

    /**
     * Remove a model from multiple teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function removeModelFromAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Check if a model is on a given team.
     */
    public function modelOnTeam(Model $model, Team|string|UnitEnum $team): bool;

    /**
     * Check if a model is on any of the specified teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool;

    /**
     * Check if a model is on all of the specified teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool;

    /**
     * Find a team by its name.
     */
    public function findTeamByName(string|UnitEnum $teamName): ?Team;

    /**
     * Get all teams.
     *
     * @return Collection<Team>
     */
    public function getAllTeams(): Collection;

    /**
     * Get all teams assigned to a model.
     *
     * @return Collection<Team>
     */
    public function getTeamsForModel(Model $model): Collection;
}
