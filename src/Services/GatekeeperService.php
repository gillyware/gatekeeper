<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\GatekeeperServiceInterface;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\ActsForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class GatekeeperService implements GatekeeperServiceInterface
{
    use ActsForGatekeeper;

    private string $lifecycleId;

    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RoleService $roleService,
        private readonly TeamService $teamService,
        private readonly GatekeeperForModelService $gatekeeperForModelService,
    ) {
        $this->setLifecycleId();
    }

    /**
     * {@inheritDoc}
     */
    public function getActor(): ?Model
    {
        $this->resolveActingAs();

        return $this->actingAs;
    }

    /**
     * {@inheritDoc}
     */
    public function getLifecycleId(): string
    {
        return $this->lifecycleId;
    }

    /**
     * {@inheritDoc}
     */
    public function setActor(Model $model): GatekeeperService
    {
        $this->actingAs($model);
        $this->propagateActor($model);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function systemActor(): GatekeeperService
    {
        return $this->setActor(new SystemActor);
    }

    /**
     * {@inheritDoc}
     */
    public function for(Model $model): GatekeeperForModelService
    {
        return $this->gatekeeperForModelService->setModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function permissionExists(string|UnitEnum $permissionName): bool
    {
        return $this->permissionService->exists($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function createPermission(string|UnitEnum $permissionName): Permission
    {
        return $this->permissionService->create($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function updatePermission(Permission|string|UnitEnum $permission, string|UnitEnum $permissionName): Permission
    {
        return $this->permissionService->update($permission, $permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivatePermission(Permission|string|UnitEnum $permission): Permission
    {
        return $this->permissionService->deactivate($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivatePermission(Permission|string|UnitEnum $permission): Permission
    {
        return $this->permissionService->reactivate($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function deletePermission(Permission|string|UnitEnum $permission): bool
    {
        return $this->permissionService->delete($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function assignPermissionToModel(Model $model, Permission|string|UnitEnum $permission): bool
    {
        return $this->permissionService->assignToModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllPermissionsToModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->assignAllToModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function revokePermissionFromModel(Model $model, Permission|string|UnitEnum $permission): bool
    {
        return $this->permissionService->revokeFromModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->revokeAllFromModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasPermission(Model $model, Permission|string|UnitEnum $permission): bool
    {
        return $this->permissionService->modelHas($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAny($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAll($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function findPermissionByName(string|UnitEnum $permissionName): ?Permission
    {
        return $this->permissionService->findByName($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissionService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectPermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectivePermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosePermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getVerboseForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function roleExists(string|UnitEnum $roleName): bool
    {
        return $this->roleService->exists($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function createRole(string|UnitEnum $roleName): Role
    {
        return $this->roleService->create($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function updateRole(Role|string|UnitEnum $role, string|UnitEnum $roleName): Role
    {
        return $this->roleService->update($role, $roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateRole(Role|string|UnitEnum $role): Role
    {
        return $this->roleService->deactivate($role);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivateRole(Role|string|UnitEnum $role): Role
    {
        return $this->roleService->reactivate($role);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteRole(Role|string|UnitEnum $role): bool
    {
        return $this->roleService->delete($role);
    }

    /**
     * {@inheritDoc}
     */
    public function assignRoleToModel(Model $model, Role|string|UnitEnum $role): bool
    {
        return $this->roleService->assignToModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllRolesToModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->assignAllToModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRoleFromModel(Model $model, Role|string|UnitEnum $role): bool
    {
        return $this->roleService->revokeFromModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->revokeAllFromModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasRole(Model $model, Role|string|UnitEnum $role): bool
    {
        return $this->roleService->modelHas($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAny($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAll($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function findRoleByName(string|UnitEnum $roleName): ?Role
    {
        return $this->roleService->findByName($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllRoles(): Collection
    {
        return $this->roleService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectRolesForModel(Model $model): Collection
    {
        return $this->roleService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveRolesForModel(Model $model): Collection
    {
        return $this->roleService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseRolesForModel(Model $model): Collection
    {
        return $this->roleService->getVerboseForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function teamExists(string|UnitEnum $teamName): bool
    {
        return $this->teamService->exists($teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function createTeam(string|UnitEnum $teamName): Team
    {
        return $this->teamService->create($teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTeam(Team|string|UnitEnum $team, string|UnitEnum $teamName): Team
    {
        return $this->teamService->update($team, $teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateTeam(Team|string|UnitEnum $team): Team
    {
        return $this->teamService->deactivate($team);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivateTeam(Team|string|UnitEnum $team): Team
    {
        return $this->teamService->reactivate($team);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTeam(Team|string|UnitEnum $team): bool
    {
        return $this->teamService->delete($team);
    }

    /**
     * {@inheritDoc}
     */
    public function addModelToTeam(Model $model, Team|string|UnitEnum $team): bool
    {
        return $this->teamService->assignToModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function addModelToAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->assignAllToModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function removeModelFromTeam(Model $model, Team|string|UnitEnum $team): bool
    {
        return $this->teamService->revokeFromModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function removeModelFromAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->revokeAllFromModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnTeam(Model $model, Team|string|UnitEnum $team): bool
    {
        return $this->teamService->modelHas($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelHasAny($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelHasAll($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function findTeamByName(string|UnitEnum $roleName): ?Team
    {
        return $this->teamService->findByName($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTeams(): Collection
    {
        return $this->teamService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getTeamsForModel(Model $model): Collection
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
