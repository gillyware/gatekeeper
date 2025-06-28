<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Support\SystemActor;
use Braxey\Gatekeeper\Traits\ActsForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
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
        $this->permissionService->actingAs($model);
        $this->roleService->actingAs($model);
        $this->teamService->actingAs($model);

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
     * Create a new permission.
     */
    public function createPermission(string $permissionName): Permission
    {
        return $this->permissionService->create($permissionName);
    }

    public function assignPermissionToModel(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->assignToModel($model, $permission);
    }

    public function assignPermissionsToModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->assignMultipleToModel($model, $permissions);
    }

    public function revokePermissionFromModel(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->revokeFromModel($model, $permission);
    }

    public function revokePermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->revokeMultipleFromModel($model, $permissions);
    }

    public function modelHasPermission(Model $model, Permission|string $permission): bool
    {
        return $this->permissionService->modelHas($model, $permission);
    }

    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAny($model, $permissions);
    }

    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAll($model, $permissions);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $roleName): Role
    {
        return $this->roleService->create($roleName);
    }

    public function assignRoleToModel(Model $model, Role|string $role): bool
    {
        return $this->roleService->assignToModel($model, $role);
    }

    public function assignRolesToModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->assignMultipleToModel($model, $roles);
    }

    public function revokeRoleFromModel(Model $model, Role|string $role): bool
    {
        return $this->roleService->revokeFromModel($model, $role);
    }

    public function revokeRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->revokeMultipleFromModel($model, $roles);
    }

    public function modelHasRole(Model $model, Role|string $role): bool
    {
        return $this->roleService->modelHas($model, $role);
    }

    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAny($model, $roles);
    }

    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAll($model, $roles);
    }

    /**
     * Create a new team.
     */
    public function createTeam(string $teamName): Team
    {
        return $this->teamService->create($teamName);
    }

    public function addModelToTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->addModelTo($model, $team);
    }

    public function addModelToTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->addModelToAll($model, $teams);
    }

    public function removeModelFromTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->removeModelFrom($model, $team);
    }

    public function removeModelFromTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->removeModelFromAll($model, $teams);
    }

    public function modelOnTeam(Model $model, Team|string $team): bool
    {
        return $this->teamService->modelOn($model, $team);
    }

    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelOnAny($model, $teams);
    }

    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelOnAll($model, $teams);
    }

    private function setLifecycleId(): void
    {
        $prefix = app()->runningInConsole() ? 'cli_' : 'request_';
        $this->lifecycleId = $prefix.Str::uuid()->toString();
    }
}
