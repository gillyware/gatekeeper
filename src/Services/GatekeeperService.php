<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class GatekeeperService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
    ) {}

    /**
     * Create a new permission.
     */
    public function createPermission(string $name): Permission
    {
        return $this->permissionRepository->add($name);
    }

    public function assignPermissionToModel(Model $model, string $permissionName): bool
    {
        return $model->assignPermission($permissionName);
    }

    public function assignPermissionsToModel(Model $model, array|Arrayable $permissionNames): bool
    {
        return $model->assignPermissions($permissionNames);
    }

    public function revokePermissionFromModel(Model $model, string $permissionName): bool
    {
        return $model->revokePermission($permissionName);
    }

    public function revokePermissionsFromModel(Model $model, array|Arrayable $permissionNames): bool
    {
        return $model->revokePermissions($permissionNames);
    }

    public function modelHasPermission(Model $model, string $permissionName): bool
    {
        return $model->hasPermission($permissionName);
    }

    public function modelHasAnyPermission(Model $model, array|Arrayable $permissionNames): bool
    {
        return $model->hasAnyPermission($permissionNames);
    }

    public function modelHasAllPermissions(Model $model, array|Arrayable $permissionNames): bool
    {
        return $model->hasAllPermissions($permissionNames);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $name): Role
    {
        if (! config('gatekeeper.features.roles', false)) {
            throw new \RuntimeException('Roles feature is disabled.');
        }

        return $this->roleRepository->add($name);
    }

    public function assignRoleToModel(Model $model, string $roleName): bool
    {
        return $model->assignRole($roleName);
    }

    public function assignRolesToModel(Model $model, array|Arrayable $roleNames): bool
    {
        return $model->assignRoles($roleNames);
    }

    public function revokeRoleFromModel(Model $model, string $roleName): bool
    {
        return $model->revokeRole($roleName);
    }

    public function revokeRolesFromModel(Model $model, array|Arrayable $roleNames): bool
    {
        return $model->revokeRoles($roleNames);
    }

    public function modelHasRole(Model $model, string $roleName): bool
    {
        return $model->hasRole($roleName);
    }

    public function modelHasAnyRole(Model $model, array|Arrayable $roleNames): bool
    {
        return $model->hasAnyRole($roleNames);
    }

    public function modelHasAllRoles(Model $model, array|Arrayable $roleNames): bool
    {
        return $model->hasAllRoles($roleNames);
    }

    /**
     * Create a new team.
     */
    public function createTeam(string $name): Team
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new \RuntimeException('Teams feature is disabled.');
        }

        return $this->teamRepository->add($name);
    }

    public function addModelToTeam(Model $model, string $teamName): bool
    {
        return $model->addToTeam($teamName);
    }

    public function addModelsToTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $model->addToTeams($teamNames);
    }

    public function removeModelFromTeam(Model $model, string $teamName): bool
    {
        return $model->removeFromTeam($teamName);
    }

    public function removeModelsFromTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $model->removeFromTeams($teamNames);
    }

    public function modelOnTeam(Model $model, string $teamName): bool
    {
        return $model->onTeam($teamName);
    }

    public function modelOnAnyTeam(Model $model, array|Arrayable $teamNames): bool
    {
        return $model->onAnyTeam($teamNames);
    }

    public function modelOnAllTeams(Model $model, array|Arrayable $teamNames): bool
    {
        return $model->onAllTeams($teamNames);
    }
}
