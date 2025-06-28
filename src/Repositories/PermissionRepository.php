<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Exceptions\PermissionNotFoundException;
use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ItemNotFoundException;
use Throwable;

class PermissionRepository
{
    public function __construct(private readonly CacheRepository $cacheRepository) {}

    /**
     * Create a new Permission instance.
     */
    public function create(string $permissionName): Permission
    {
        $permission = new Permission(['name' => $permissionName]);

        if ($permission->save()) {
            $this->cacheRepository->forget($this->getCacheKeyForAll());
        }

        return $permission;
    }

    /**
     * Get all permissions.
     */
    public function all(): Collection
    {
        $permissions = $this->cacheRepository->get($this->getCacheKeyForAll());

        if ($permissions) {
            return collect($permissions);
        }

        $permissions = Permission::all();

        $this->cacheRepository->put($this->getCacheKeyForAll(), $permissions);

        return $permissions;
    }

    /**
     * Find a permission by its name.
     */
    public function findByName(string $permissionName): Permission
    {
        try {
            return $this->all()->where('name', $permissionName)->firstOrFail();
        } catch (ItemNotFoundException) {
            throw new PermissionNotFoundException($permissionName);
        } catch (Throwable $t) {
            throw $t;
        }
    }

    /**
     * Get all active permissions.
     */
    public function getActive(): Collection
    {
        return $this->all()->filter(fn (Permission $permission) => $permission->is_active);
    }

    /**
     * Get active permissions where the name is in the provided array or collection.
     */
    public function getActiveWhereNameIn(array|Collection $permissionNames): Collection
    {
        return $this->getActive()->whereIn('name', $permissionNames);
    }

    /**
     * Get active permissions for a specific model.
     */
    public function getActiveForModel(Model $model): Collection
    {
        $activeNamesForModel = $this->getActiveNamesForModel($model);

        return $this->getActiveWhereNameIn($activeNamesForModel);
    }

    /**
     * Get active permission names for a specific model.
     */
    public function getActiveNamesForModel(Model $model): Collection
    {
        $cacheKey = $this->getCacheKeyForModel($model);

        $activePermissionNames = $this->cacheRepository->get($cacheKey);

        if ($activePermissionNames) {
            return collect($activePermissionNames);
        }

        $permissionsTable = Config::get('gatekeeper.tables.permissions');
        $modelHasPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions');

        $activePermissionNames = $model->permissions()
            ->select("$permissionsTable.*")
            ->where('is_active', true)
            ->whereNull("$modelHasPermissionsTable.deleted_at")
            ->pluck("$permissionsTable.name");

        $this->cacheRepository->put($cacheKey, $activePermissionNames);

        return $activePermissionNames;
    }

    /**
     * Invalidate the cache for all permissions.
     */
    public function invalidateCacheForModel(Model $model): void
    {
        $this->cacheRepository->forget($this->getCacheKeyForModel($model));
    }

    /**
     * Invalidate the cache for all permissions.
     */
    private function getCacheKeyForAll(): string
    {
        return 'permissions';
    }

    /**
     * Get the cache key for a specific model's permissions.
     */
    private function getCacheKeyForModel(Model $model): string
    {
        return "permissions.{$model->getMorphClass()}.{$model->getKey()}";
    }
}
