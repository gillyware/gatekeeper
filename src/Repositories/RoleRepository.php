<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Exceptions\RoleNotFoundException;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ItemNotFoundException;
use Throwable;

class RoleRepository
{
    public function __construct(private readonly CacheRepository $cacheRepository) {}

    /**
     * Create a new Role instance.
     */
    public function create(string $roleName): Role
    {
        $role = new Role(['name' => $roleName]);

        if ($role->save()) {
            $this->cacheRepository->forget($this->getCacheKeyForAll());
        }

        return $role;
    }

    /**
     * Get all roles.
     */
    public function all(): Collection
    {
        $roles = $this->cacheRepository->get($this->getCacheKeyForAll());

        if ($roles) {
            return collect($roles);
        }

        $roles = Role::all();

        $this->cacheRepository->put($this->getCacheKeyForAll(), $roles);

        return $roles;
    }

    /**
     * Find a role by its name.
     */
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

    /**
     * Get active roles where the name is in the provided array or collection.
     */
    public function getActiveWhereNameIn(array|Collection $roleNames): Collection
    {
        return $this->getActive()->whereIn('name', $roleNames);
    }

    /**
     * Get active roles for a specific model.
     */
    public function getActiveForModel(Model $model): Collection
    {
        $activeNamesForModel = $this->getActiveNamesForModel($model);

        return $this->getActiveWhereNameIn($activeNamesForModel);
    }

    /**
     * Get active role names for a specific model.
     */
    public function getActiveNamesForModel(Model $model): Collection
    {
        $cacheKey = $this->getCacheKeyForModel($model);

        $activeRoleNames = $this->cacheRepository->get($cacheKey);

        if ($activeRoleNames) {
            return collect($activeRoleNames);
        }

        $rolesTable = Config::get('gatekeeper.tables.roles');
        $modelHasRolesTable = Config::get('gatekeeper.tables.model_has_roles');

        $activeRoleNames = $model->roles()
            ->select("$rolesTable.*")
            ->where('is_active', true)
            ->whereNull("$modelHasRolesTable.deleted_at")
            ->pluck("$rolesTable.name");

        $this->cacheRepository->put($cacheKey, $activeRoleNames);

        return $activeRoleNames;
    }

    /**
     * Invalidate the cache for a specific model.
     */
    public function invalidateCacheForModel(Model $model): void
    {
        $this->cacheRepository->forget($this->getCacheKeyForModel($model));
    }

    /**
     * Invalidate the cache for all roles.
     */
    private function getCacheKeyForAll(): string
    {
        return 'roles';
    }

    /**
     * Get the cache key for a specific model.
     */
    private function getCacheKeyForModel(Model $model): string
    {
        return "roles.{$model->getMorphClass()}.{$model->getKey()}";
    }
}
