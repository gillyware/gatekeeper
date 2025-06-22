<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Exceptions\Permission\PermissionNotFoundException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class PermissionRepository
{
    public function __construct(private readonly CacheService $cacheService) {}

    /**
     * Check if a permission with the given name exists.
     */
    public function exists(string $permissionName): bool
    {
        return Permission::query()->where('name', $permissionName)->exists();
    }

    /**
     * Find a permission by its name.
     */
    public function findByName(string $permissionName): ?Permission
    {
        return $this->all()->firstWhere('name', $permissionName);
    }

    /**
     * Find a permission by its name, or fail.
     */
    public function findOrFailByName(string $permissionName): Permission
    {
        $permission = $this->findByName($permissionName);

        if (! $permission) {
            throw new PermissionNotFoundException($permissionName);
        }

        return $permission;
    }

    /**
     * Create a new Permission instance.
     */
    public function create(string $permissionName): Permission
    {
        $permission = new Permission(['name' => $permissionName]);

        if ($permission->save()) {
            $this->cacheService->invalidateCacheForAllPermissions();
        }

        return $permission;
    }

    /**
     * Update an existing permission.
     */
    public function update(Permission $permission, string $permissionName): Permission
    {
        if ($permission->update(['name' => $permissionName])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Deactivate a permission.
     */
    public function deactivate(Permission $permission): Permission
    {
        if ($permission->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Reactivate a permission.
     */
    public function reactivate(Permission $permission): Permission
    {
        if ($permission->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Delete a permission.
     */
    public function delete(Permission $permission): bool
    {
        $deleted = $permission->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all permissions.
     */
    public function all(): Collection
    {
        $permissions = $this->cacheService->getAllPermissions();

        if ($permissions) {
            return $permissions;
        }

        $permissions = Permission::all()->values();

        $this->cacheService->putAllPermissions($permissions);

        return $permissions;
    }

    /**
     * Get all active permissions.
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Permission $permission) => $permission->is_active)->values();
    }

    /**
     * Get all permissions where the name is in the provided array or collection.
     */
    public function whereNameIn(array|Collection $permissionNames): Collection
    {
        return $this->all()->whereIn('name', $permissionNames)->values();
    }

    /**
     * Get all permission names for a specific model.
     */
    public function namesForModel(Model $model): Collection
    {
        $allPermissionNames = $this->cacheService->getModelPermissionNames($model);

        if ($allPermissionNames) {
            return $allPermissionNames;
        }

        $permissionsTable = Config::get('gatekeeper.tables.permissions');
        $modelHasPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions');

        $allPermissionNames = $model->permissions()
            ->select("$permissionsTable.*")
            ->whereNull("$modelHasPermissionsTable.deleted_at")
            ->pluck("$permissionsTable.name")
            ->values();

        $this->cacheService->putModelPermissionNames($model, $allPermissionNames);

        return $allPermissionNames;
    }

    /**
     * Get all permissions for a specific model.
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active permissions for a specific model.
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Permission $permission) => $permission->is_active)
            ->values();
    }

    /**
     * Find a permission by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $permissionName): ?Permission
    {
        return $this->forModel($model)->firstWhere('name', $permissionName);
    }
}
