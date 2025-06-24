<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class GatekeeperService
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RoleService $roleService,
        private readonly TeamService $teamService,
    ) {}

    /**
     * Create a new permission.
     */
    public function createPermission(string $name): Permission
    {
        return $this->permissionService->create($name);
    }

    public function assignPermissionToModel(Model $model, string $permissionName): bool
    {
        return $this->permissionService->assignToModel($model, $permissionName);
    }

    public function assignPermissionsToModel(Model $model, array|Arrayable $permissionNames): bool
    {
        return $this->permissionService->assignMultipleToModel($model, $permissionNames);
    }

    public function revokePermissionFromModel(Model $model, string $permissionName): bool
    {
        return $this->permissionService->revokeFromModel($model, $permissionName);
    }

    public function revokePermissionsFromModel(Model $model, array|Arrayable $permissionNames): bool
    {
        return $this->permissionService->revokeMultipleFromModel($model, $permissionNames);
    }

    public function modelHasPermission(Model $model, string $permissionName): bool
    {
        return $this->permissionService->modelHas($model, $permissionName);
    }

    public function modelHasAnyPermission(Model $model, array|Arrayable $permissionNames): bool
    {
        return $this->permissionService->modelHasAny($model, $permissionNames);
    }

    public function modelHasAllPermissions(Model $model, array|Arrayable $permissionNames): bool
    {
        return $this->permissionService->modelHasAll($model, $permissionNames);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $name): Role
    {
        return $this->roleService->create($name);
    }

    public function assignRoleToModel(Model $model, string $roleName): bool
    {
        return $this->roleService->assignToModel($model, $roleName);
    }

    public function assignRolesToModel(Model $model, array|Arrayable $roleNames): bool
    {
        return $this->roleService->assignMultipleToModel($model, $roleNames);
    }

    public function revokeRoleFromModel(Model $model, string $roleName): bool
    {
        return $this->roleService->revokeFromModel($model, $roleName);
    }

    public function revokeRolesFromModel(Model $model, array|Arrayable $roleNames): bool
    {
        return $this->roleService->revokeMultipleFromModel($model, $roleNames);
    }

    public function modelHasRole(Model $model, string $roleName): bool
    {
        return $this->roleService->modelHas($model, $roleName);
    }

    public function modelHasAnyRole(Model $model, array|Arrayable $roleNames): bool
    {
        return $this->roleService->modelHasAny($model, $roleNames);
    }

    public function modelHasAllRoles(Model $model, array|Arrayable $roleNames): bool
    {
        return $this->roleService->modelHasAll($model, $roleNames);
    }

    /**
     * Create a new team.
     */
    public function createTeam(string $name): Team
    {
        return $this->teamService->create($name);
    }

    public function addModelToTeam(Model $model, string $teamName): bool
    {
        return $this->teamService->addModelTo($model, $teamName);
    }

    public function addModelToTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $this->teamService->addModelToAll($model, $teamNames);
    }

    public function removeModelFromTeam(Model $model, string $teamName): bool
    {
        return $this->teamService->removeModelFrom($model, $teamName);
    }

    public function removeModelFromTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $this->teamService->removeModelFromAll($model, $teamNames);
    }

    public function modelOnTeam(Model $model, string $teamName): bool
    {
        return $this->teamService->modelOn($model, $teamName);
    }

    public function modelOnAnyTeam(Model $model, array|Arrayable $teamNames): bool
    {
        return $this->teamService->modelOnAny($model, $teamNames);
    }

    public function modelOnAllTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $this->teamService->modelOnAll($model, $teamNames);
    }
}
