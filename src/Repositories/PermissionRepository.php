<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionNotFoundException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Permission>
 */
class PermissionRepository implements EntityRepositoryInterface
{
    use EnforcesForGatekeeper;

    public function __construct(private readonly CacheService $cacheService) {}

    /**
     * Check if the permissions table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS));
    }

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
        return $this->all()->get($permissionName);
    }

    /**
     * Find a permission by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $permissionName): ?Permission
    {
        return $this->forModel($model)->get($permissionName);
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
     * Create a new permission.
     */
    public function create(string $permissionName): Permission
    {
        $permission = new Permission(['name' => $permissionName]);

        if ($permission->save()) {
            $this->cacheService->invalidateCacheForAllPermissions();
        }

        return $permission->fresh();
    }

    /**
     * Update an existing permission.
     *
     * @param  Permission  $permission
     */
    public function update($permission, string $newPermissionName): Permission
    {
        if ($permission->update(['name' => $newPermissionName])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Deactivate a permission.
     *
     * @param  Permission  $permission
     */
    public function deactivate($permission): Permission
    {
        if ($permission->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Reactivate a permission.
     *
     * @param  Permission  $permission
     */
    public function reactivate($permission): Permission
    {
        if ($permission->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Delete a permission.
     *
     * @param  Permission  $permission
     */
    public function delete($permission): bool
    {
        $deleted = $permission->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all permissions.
     *
     * @return Collection<Permission>
     */
    public function all(): Collection
    {
        $permissions = $this->cacheService->getAllPermissions();

        if ($permissions) {
            return $permissions;
        }

        $permissions = Permission::all()->mapWithKeys(fn (Permission $p) => [$p->name => $p]);

        $this->cacheService->putAllPermissions($permissions);

        return $permissions;
    }

    /**
     * Get all active permissions.
     *
     * @return Collection<Permission>
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Permission $permission) => $permission->is_active);
    }

    /**
     * Get all permissions where the name is in the provided array or collection.
     *
     * @return Collection<Permission>
     */
    public function whereNameIn(array|Collection $permissionNames): Collection
    {
        return $this->all()->whereIn('name', $permissionNames);
    }

    /**
     * Get all permission names for a specific model.
     *
     * @return Collection<string>
     */
    public function namesForModel(Model $model): Collection
    {
        $allPermissionNames = $this->cacheService->getModelPermissionNames($model);

        if ($allPermissionNames) {
            return $allPermissionNames;
        }

        if (! $this->modelInteractsWithPermissions($model)) {
            return collect();
        }

        $permissionsTable = Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
        $modelHasPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS);

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
     *
     * @return Collection<Permission>
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active permissions for a specific model.
     *
     * @return Collection<Permission>
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Permission $permission) => $permission->is_active);
    }

    /**
     * Get a page of permissions.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Permission::query()->whereLike('name', "%{$packet->searchTerm}%");

        if ($packet->prioritizedAttribute === 'is_active') {
            $query = $query
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('name', $packet->nameOrder);
        } else {
            $query = $query
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder);
        }

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }
}
