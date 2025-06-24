<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithPermissionsException;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Braxey\Gatekeeper\Traits\InteractsWithPermissions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class PermissionService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    public function create(string $permissionName): Permission
    {
        return $this->permissionRepository->create($permissionName);
    }

    /**
     * Assign a permission to a model.
     */
    public function assignToModel(Model $model, string $permissionName): bool
    {
        $this->forcePermissionInteraction($model);

        $permission = $this->permissionRepository->findByName($permissionName);

        // If the model already has this permission directly assigned, we don't need to sync again.
        if ($this->modelDirectlyHasPermission($model, $permission)) {
            return true;
        }

        // Insert the permission assignment.
        $this->modelHasPermissionRepository->create($model, $permission);

        // Invalidate the permissions cache for the model.
        $this->permissionRepository->invalidateCacheForModel($model);

        return true;
    }

    /**
     * Assign multiple permissions to a model.
     */
    public function assignMultipleToModel(Model $model, array|Arrayable $permissionNames): bool
    {
        $result = true;

        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            $result = $result && $this->assignToModel($model, $permissionName);
        }

        return $result;
    }

    /**
     * Revoke a permission from a model.
     */
    public function revokeFromModel(Model $model, string $permissionName): bool
    {
        $this->forcePermissionInteraction($model);

        $permission = $this->permissionRepository->findByName($permissionName);

        if ($this->modelHasPermissionRepository->deleteForModelAndPermission($model, $permission)) {
            // Invalidate the permissions cache for the model.
            $this->permissionRepository->invalidateCacheForModel($model);

            return true;
        }

        return false;
    }

    /**
     * Revoke multiple permissions from a model.
     */
    public function revokeMultipleFromModel(Model $model, array|Arrayable $permissionNames): bool
    {
        $result = true;

        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            $result = $result && $this->revokeFromModel($model, $permissionName);
        }

        return $result;
    }

    /**
     * Check if a model has a given permission.
     */
    public function modelHas(Model $model, string $permissionName): bool
    {
        $this->forcePermissionInteraction($model);

        $permission = $this->permissionRepository->findByName($permissionName);

        // If the permission is not active, we can immediately return false.
        if (! $permission->is_active) {
            return false;
        }

        // Fetch the most recent permission assignment.
        $recentPermissionAssignment = $this->modelHasPermissionRepository->getRecentForModelAndPermissionIncludingTrashed($model, $permission);

        // If we find a direct permission assignment, we can use it to determine if the model has the permission.
        if ($recentPermissionAssignment) {
            return ! $recentPermissionAssignment->deleted_at;
        }

        // If roles are enabled, check if the model has the permission through roles.
        if (config('gatekeeper.features.roles', false)) {
            $hasRoleWithPermission = $this->roleRepository
                ->getActiveForModel($model)
                ->some(fn (Role $role) => $role->hasPermission($permission->name));

            // If the model has any active roles with the permission, return true.
            if ($hasRoleWithPermission) {
                return true;
            }
        }

        // If teams are enabled, check if the model has the permission through the teams roles or permissions.
        if (config('gatekeeper.features.teams', false)) {
            $onTeamWithPermission = $this->teamRepository
                ->getActiveForModel($model)
                ->some(fn (Team $team) => $team->hasPermission($permission->name));

            // If the model has any active teams with the permission, return true.
            if ($onTeamWithPermission) {
                return true;
            }
        }

        // Return false by default.
        return false;
    }

    /**
     * Check if a model has any of the given permissions.
     */
    public function modelHasAny(Model $model, array|Arrayable $permissionNames): bool
    {
        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            if ($this->modelHas($model, $permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model has all of the given permissions.
     */
    public function modelHasAll(Model $model, array|Arrayable $permissionNames): bool
    {
        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            if (! $this->modelHas($model, $permissionName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a model has a permission directly assigned.
     */
    private function modelDirectlyHasPermission(Model $model, Permission $permission): bool
    {
        // Check if the model has the permission directly assigned.
        $recentPermissionAssignment = $this->modelHasPermissionRepository->getRecentForModelAndPermissionIncludingTrashed($model, $permission);

        // If the permission is currently directly assigned to the model, return true.
        return $recentPermissionAssignment && ! $recentPermissionAssignment->deleted_at;
    }

    /**
     * Force the model to interact with permissions.
     */
    private function forcePermissionInteraction(Model $model): void
    {
        if (! in_array(InteractsWithPermissions::class, class_uses_recursive($model))) {
            throw new ModelDoesNotInteractWithPermissionsException($model);
        }
    }

    /**
     * Convert an array or Arrayable object of permission names to an array.
     */
    private function permissionNamesArray(array|Arrayable $permissionNames): array
    {
        return $permissionNames instanceof Arrayable ? $permissionNames->toArray() : $permissionNames;
    }
}
