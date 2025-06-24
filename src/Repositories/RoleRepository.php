<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Exceptions\RoleNotFoundException;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ItemNotFoundException;
use Throwable;

class RoleRepository
{
    public function create(string $roleName): Role
    {
        $role = new Role(['name' => $roleName]);

        if ($role->save()) {
            Cache::forget($this->getCacheKeyForAll());
        }

        return $role;
    }

    public function all(): Collection
    {
        $roles = Cache::get($this->getCacheKeyForAll());

        if ($roles) {
            return collect($roles);
        }

        $roles = Role::all();

        Cache::put($this->getCacheKeyForAll(), $roles, config('gatekeeper.cache.ttl', 2 * 60 * 60));

        return $roles;
    }

    public function findByName(string $roleName): Role
    {
        try {
            return $this->all()->where('name', $roleName)->firstOrFail();
        } catch (ItemNotFoundException) {
            throw new RoleNotFoundException($roleName);
        } catch (Throwable $t) {
            throw $t;
        }
    }

    /**
     * Get all active roles.
     */
    public function getActive(): Collection
    {
        return $this->all()->filter(fn (Role $role) => $role->is_active);
    }

    public function getActiveWhereNameIn(array|Collection $roleNames): Collection
    {
        return $this->getActive()->whereIn('name', $roleNames);
    }

    public function getActiveForModel(Model $model): Collection
    {
        $activeNamesForModel = $this->getActiveNamesForModel($model);

        return $this->getActiveWhereNameIn($activeNamesForModel);
    }

    public function getActiveNamesForModel(Model $model): Collection
    {
        $cacheKey = $this->getCacheKeyForModel($model);

        $activeRoleNames = Cache::get($cacheKey);

        if ($activeRoleNames) {
            return collect($activeRoleNames);
        }

        $rolesTable = config('gatekeeper.tables.roles', 'roles');

        $activeRoleNames = $model->roles()
            ->select("$rolesTable.*")
            ->where('is_active', true)
            ->whereNull('model_has_roles.deleted_at')
            ->pluck("$rolesTable.name");

        Cache::put($cacheKey, $activeRoleNames, config('gatekeeper.cache.ttl', 2 * 60 * 60));

        return $activeRoleNames;
    }

    public function invalidateCacheForModel(Model $model): void
    {
        Cache::forget($this->getCacheKeyForModel($model));
    }

    private function getCacheKeyForAll(): string
    {
        return 'gatekeeper.roles';
    }

    private function getCacheKeyForModel(Model $model): string
    {
        return "gatekeeper.roles.{$model->getMorphClass()}.{$model->getKey()}";
    }
}
