<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Exceptions\Role\RoleNotFoundException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class RoleRepository
{
    public function __construct(private readonly CacheService $cacheService) {}

    /**
     * Check if a role with the given name exists.
     */
    public function exists(string $roleName): bool
    {
        return Role::query()->where('name', $roleName)->exists();
    }

    /**
     * Find a role by its name.
     */
    public function findByName(string $roleName): ?Role
    {
        return $this->all()->firstWhere('name', $roleName);
    }

    /**
     * Find a role by its name, or fail.
     */
    public function findOrFailByName(string $roleName): Role
    {
        $role = $this->findByName($roleName);

        if (! $role) {
            throw new RoleNotFoundException($roleName);
        }

        return $role;
    }

    /**
     * Create a new Role instance.
     */
    public function create(string $roleName): Role
    {
        $role = new Role(['name' => $roleName]);

        if ($role->save()) {
            $this->cacheService->invalidateCacheForAllRoles();
        }

        return $role;
    }

    /**
     * Update an existing role.
     */
    public function update(Role $role, string $roleName): Role
    {
        if ($role->update(['name' => $roleName])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Deactivate a role.
     */
    public function deactivate(Role $role): Role
    {
        if ($role->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Reactivate a role.
     */
    public function reactivate(Role $role): Role
    {
        if ($role->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): bool
    {
        $deleted = $role->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all roles.
     */
    public function all(): Collection
    {
        $roles = $this->cacheService->getAllRoles();

        if ($roles) {
            return $roles;
        }

        $roles = Role::all()->values();

        $this->cacheService->putAllRoles($roles);

        return $roles;
    }

    /**
     * Get all active roles.
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Role $role) => $role->is_active)->values();
    }

    /**
     * Get all roles where the name is in the provided array or collection.
     */
    public function whereNameIn(array|Collection $roleNames): Collection
    {
        return $this->all()->whereIn('name', $roleNames)->values();
    }

    /**
     * Get all role names for a specific model.
     */
    public function namesForModel(Model $model): Collection
    {
        $allRoleNames = $this->cacheService->getModelRoleNames($model);

        if ($allRoleNames) {
            return $allRoleNames;
        }

        $rolesTable = Config::get('gatekeeper.tables.roles');
        $modelHasRolesTable = Config::get('gatekeeper.tables.model_has_roles');

        $allRoleNames = $model->roles()
            ->select("$rolesTable.*")
            ->whereNull("$modelHasRolesTable.deleted_at")
            ->pluck("$rolesTable.name")
            ->values();

        $this->cacheService->putModelRoleNames($model, $allRoleNames);

        return $allRoleNames;
    }

    /**
     * Get all roles for a specific model.
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active roles for a specific model.
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Role $role) => $role->is_active)
            ->values();
    }

    /**
     * Find a role by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $roleName): ?Role
    {
        return $this->forModel($model)->firstWhere('name', $roleName);
    }
}
