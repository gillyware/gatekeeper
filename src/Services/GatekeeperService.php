<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\ActsForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GatekeeperService
{
    use ActsForGatekeeper;

    private string $lifecycleId;

    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RoleService $roleService,
        private readonly TeamService $teamService,
    ) {
        $this->setLifecycleId();
    }

    /**
     * Get the currently acting as model.
     */
    public function getActor(): ?Model
    {
        $this->resolveActingAs();

        return $this->actingAs;
    }

    /**
     * Get the lifecycle ID for the current request or CLI execution.
     */
    public function getLifecycleId(): string
    {
        return $this->lifecycleId;
    }

    /**
     * Set the acting as model.
     */
    public function setActor(Model $model): static
    {
        $this->actingAs($model);
        $this->propagateActor($model);

        return $this;
    }

    /**
     * Set the actor to a system actor.
     */
    public function systemActor(): static
    {
        return $this->setActor(new SystemActor);
    }

    /**
     * Check if a permission exists.
     */
    public function permissionExists(string $permissionName): bool
    {
        return $this->permissionService->exists($permissionName);
    }

    /**
     * Create a new permission.
     */
    public function createPermission(string $permissionName): Permission
    {
        return $this->permissionService->create($permissionName);
    }

    /**
     * Update an existing permission.
     */
    public function updatePermission(Permission $permission, string $permissionName): Permission
    {
        return $this->permissionService->update($permission, $permissionName);
    }

    /**
     * Deactivate a permission.
     */
    public function deactivatePermission(Permission $permission): Permission
    {
        return $this->permissionService->deactivate($permission);
    }

    /**
     * Reactivate a permission.
     */
    public function reactivatePermission(Permission $permission): Permission
    {
        return $this->permissionService->reactivate($permission);
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission|string $permission): bool
    {
        return $this->permissionService->delete($permission);
    }

    /**
     * Assign a permission to a model.
     */
    public function assignPermissionToModel(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->assignToModel($model, $permission);
    }

    /**
     * Assign multiple permissions to a model.
     */
    public function assignPermissionsToModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->assignMultipleToModel($model, $permissions);
    }

    /**
     * Revoke a permission from a model.
     */
    public function revokePermissionFromModel(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->revokeFromModel($model, $permission);
    }

    /**
     * Revoke multiple permissions from a model.
     */
    public function revokePermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->revokeMultipleFromModel($model, $permissions);
    }

    /**
     * Check if a model has a specific permission.
     */
    public function modelHasPermission(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->modelHas($model, $permission);
    }

    /**
     * Check if a model has any of the specified permissions.
     */
    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAny($model, $permissions);
    }

    /**
     * Check if a model has all of the specified permissions.
     */
    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAll($model, $permissions);
    }

    /**
     * Find a permission by its name.
     */
    public function findPermissionByName(string $permissionName): ?Permission
    {
        return $this->permissionService->findByName($permissionName);
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissionService->getAll();
    }

    /**
     * Get all permissions directly assigned to a model.
     */
    public function getDirectPermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getDirectForModel($model);
    }

    /**
     * Get effective permissions for a model.
     */
    public function getEffectivePermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getEffectiveForModel($model);
    }

    /**
     * Check if a role exists.
     */
    public function roleExists(string $roleName): bool
    {
        return $this->roleService->exists($roleName);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $roleName): Role
    {
        return $this->roleService->create($roleName);
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Role $role, string $roleName): Role
    {
        return $this->roleService->update($role, $roleName);
    }

    /**
     * Deactivate a role.
     */
    public function deactivateRole(Role $role): Role
    {
        return $this->roleService->deactivate($role);
    }

    /**
     * Reactivate a role.
     */
    public function reactivateRole(Role $role): Role
    {
        return $this->roleService->reactivate($role);
    }

    /**
     * Delete a role.
     */
    public function deleteRole(Role|string $role): bool
    {
        return $this->roleService->delete($role);
    }

    /**
     * Assign a role to a model.
     */
    public function assignRoleToModel(Model $model, Role|string $role): bool
    {
        return $this->roleService->assignToModel($model, $role);
    }

    /**
     * Assign multiple roles to a model.
     */
    public function assignRolesToModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->assignMultipleToModel($model, $roles);
    }

    /**
     * Revoke a role from a model.
     */
    public function revokeRoleFromModel(Model $model, Role|string $role): bool
    {
        return $this->roleService->revokeFromModel($model, $role);
    }

    /**
     * Revoke multiple roles from a model.
     */
    public function revokeRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->revokeMultipleFromModel($model, $roles);
    }

    /**
     * Check if a model has a specific role.
     */
    public function modelHasRole(Model $model, Role|string $role): bool
    {
        return $this->roleService->modelHas($model, $role);
    }

    /**
     * Check if a model has any of the specified roles.
     */
    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAny($model, $roles);
    }

    /**
     * Check if a model has all of the specified roles.
     */
    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAll($model, $roles);
    }

    /**
     * Find a role by its name.
     */
    public function findRoleByName(string $roleName): ?Role
    {
        return $this->roleService->findByName($roleName);
    }

    /**
     * Get all roles.
     */
    public function getAllRoles(): Collection
    {
        return $this->roleService->getAll();
    }

    /**
     * Get all roles directly assigned to a model.
     */
    public function getDirectRolesForModel(Model $model): Collection
    {
        return $this->roleService->getDirectForModel($model);
    }

    /**
     * Get effective roles for a model.
     */
    public function getEffectiveRolesForModel(Model $model): Collection
    {
        return $this->roleService->getEffectiveForModel($model);
    }

    /**
     * Check if a team exists.
     */
    public function teamExists(string $teamName): bool
    {
        return $this->teamService->exists($teamName);
    }

    /**
     * Create a new team.
     */
    public function createTeam(string $teamName): Team
    {
        return $this->teamService->create($teamName);
    }

    /**
     * Update an existing team.
     */
    public function updateTeam(Team $team, string $teamName): Team
    {
        return $this->teamService->update($team, $teamName);
    }

    /**
     * Deactivate a team.
     */
    public function deactivateTeam(Team $team): Team
    {
        return $this->teamService->deactivate($team);
    }

    /**
     * Reactivate a team.
     */
    public function reactivateTeam(Team $team): Team
    {
        return $this->teamService->reactivate($team);
    }

    /**
     * Delete a team.
     */
    public function deleteTeam(Team|string $team): bool
    {
        return $this->teamService->delete($team);
    }

    /**
     * Add a model to a team.
     */
    public function addModelToTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->addModelTo($model, $team);
    }

    /**
     * Add a model to multiple teams.
     */
    public function addModelToTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->addModelToAll($model, $teams);
    }

    /**
     * Remove a model from a team.
     */
    public function removeModelFromTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->removeModelFrom($model, $team);
    }

    /**
     * Remove a model from multiple teams.
     */
    public function removeModelFromTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->removeModelFromAll($model, $teams);
    }

    /**
     * Check if a model is on a specific team.
     */
    public function modelOnTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->modelOn($model, $team);
    }

    /**
     * Check if a model is on any of the specified teams.
     */
    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelOnAny($model, $teams);
    }

    /**
     * Check if a model is on all of the specified teams.
     */
    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelOnAll($model, $teams);
    }

    /**
     * Find a team by its name.
     */
    public function findTeamByName(string $roleName): ?Role
    {
        return $this->teamService->findByName($roleName);
    }

    /**
     * Get all teams.
     */
    public function getAllTeams(): Collection
    {
        return $this->teamService->getAll();
    }

    /**
     * Get all teams directly assigned to a model.
     */
    public function getDirectTeamsForModel(Model $model): Collection
    {
        return $this->teamService->getDirectForModel($model);
    }

    /**
     * Set the lifecycle ID for the current request or CLI execution.
     */
    private function setLifecycleId(): void
    {
        $prefix = app()->runningInConsole() ? 'cli_' : 'request_';
        $this->lifecycleId = $prefix.Str::uuid()->toString();
    }

    /**
     * Propagate the acting as model to the services.
     */
    private function propagateActor(Model $model): void
    {
        $this->permissionService->actingAs($model);
        $this->roleService->actingAs($model);
        $this->teamService->actingAs($model);
    }
}
