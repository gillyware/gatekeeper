<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Exceptions\PermissionNotFoundException;
use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ItemNotFoundException;
use Throwable;

class PermissionRepository
{
    public function all(): Collection
    {
        $permissions = Cache::get('gatekeeper.permissions');

        if ($permissions) {
            return collect($permissions);
        }

        $permissions = Permission::all();

        Cache::put('gatekeeper.permissions', $permissions, config('gatekeeper.cache.ttl', 24 * 60 * 60));

        return $permissions;
    }

    public function findByName(string $permissionName): Permission
    {
        try {
            return $this->all()->where('name', $permissionName)->firstOrFail();
        } catch (ItemNotFoundException $e) {
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

    public function getActiveForModel(Model $model): Collection
    {
        $activeNamesForModel = $this->getActiveNamesForModel($model);

        return $this->getActiveWhereNameIn($activeNamesForModel);
    }

    public function getActiveWhereNameIn(array|Collection $permissionNames): Collection
    {
        return $this->getActive()->whereIn('name', $permissionNames);
    }

    public function getActiveNamesForModel(Model $model): Collection
    {
        $cacheKey = "gatekeeper.permissions.{$model->getMorphClass()}.{$model->getKey()}";

        $activePermissionNames = Cache::get($cacheKey);

        if ($activePermissionNames) {
            return collect($activePermissionNames);
        }

        $permissionsTable = config('gatekeeper.tables.permissions', 'permissions');

        $activePermissionNames = $model->permissions()
            ->select("$permissionsTable.*")
            ->where('is_active', true)
            ->whereNull('model_has_permissions.deleted_at')
            ->pluck("$permissionsTable.name");

        Cache::put($cacheKey, $activePermissionNames, config('gatekeeper.cache.ttl', 24 * 60 * 60));

        return $activePermissionNames;
    }
}
