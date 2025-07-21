<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Role\RoleNotFoundException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Role>
 */
class RoleRepository implements EntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    /**
     * Check if the roles table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES));
    }

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
        return $this->all()->get($roleName);
    }

    /**
     * Find a role by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $roleName): ?Role
    {
        return $this->forModel($model)->get($roleName);
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
     * Create a new role.
     */
    public function create(string $roleName): Role
    {
        $role = new Role(['name' => $roleName]);

        if ($role->save()) {
            $this->cacheService->invalidateCacheForAllRoles();
        }

        return $role->fresh();
    }

    /**
     * Update an existing role.
     *
     * @param  Role  $role
     */
    public function update($role, string $newRoleName): Role
    {
        if ($role->update(['name' => $newRoleName])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Deactivate a role.
     *
     * @param  Role  $role
     */
    public function deactivate($role): Role
    {
        if ($role->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Reactivate a role.
     *
     * @param  Role  $role
     */
    public function reactivate($role): Role
    {
        if ($role->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Delete a role.
     *
     * @param  Role  $role
     */
    public function delete($role): bool
    {
        // Unassign all permissions from the role (without audit logging).
        $this->modelHasPermissionRepository->deleteForModel($role);

        $deleted = $role->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all roles.
     *
     * @return Collection<Role>
     */
    public function all(): Collection
    {
        $roles = $this->cacheService->getAllRoles();

        if ($roles) {
            return $roles;
        }

        $roles = Role::all()->mapWithKeys(fn (Role $r) => [$r->name => $r]);

        $this->cacheService->putAllRoles($roles);

        return $roles;
    }

    /**
     * Get all active roles.
     *
     * @return Collection<Role>
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Role $role) => $role->is_active);
    }

    /**
     * Get all roles where the name is in the provided array or collection.
     *
     * @return Collection<Role>
     */
    public function whereNameIn(array|Collection $roleNames): Collection
    {
        return $this->all()->whereIn('name', $roleNames);
    }

    /**
     * Get all role names for a specific model.
     *
     * @return Collection<string>
     */
    public function namesForModel(Model $model): Collection
    {
        $allRoleNames = $this->cacheService->getModelRoleNames($model);

        if ($allRoleNames) {
            return $allRoleNames;
        }

        $rolesTable = Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
        $modelHasRolesTable = Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES);

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
     *
     * @return Collection<Role>
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active roles for a specific model.
     *
     * @return Collection<Role>
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Role $role) => $role->is_active);
    }

    /**
     * Get a page of roles.
     */
    public function getPage(int $pageNumber, string $searchTerm, string $importantAttribute, string $nameOrder, string $isActiveOrder): LengthAwarePaginator
    {
        $query = Role::query()->whereLike('name', "%{$searchTerm}%");

        if ($importantAttribute === 'is_active') {
            $query = $query
                ->orderBy('is_active', $isActiveOrder)
                ->orderBy('name', $nameOrder);
        } else {
            $query = $query
                ->orderBy('name', $nameOrder)
                ->orderBy('is_active', $isActiveOrder);
        }

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }
}
