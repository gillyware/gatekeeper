<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Role\RoleNotFoundException;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Role>
 */
class RoleRepository implements EntityRepositoryInterface
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    /**
     * Check if the roles table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable((new Role)->getTable());
    }

    /**
     * Check if a role with the given name exists.
     */
    public function exists(string $roleName): bool
    {
        return Role::query()->where('name', $roleName)->exists();
    }

    /**
     * Get all roles.
     *
     * @return Collection<string, Role>
     */
    public function all(): Collection
    {
        $roles = $this->cacheService->getAllRoles();

        if ($roles) {
            return $roles;
        }

        $roles = Role::all()->mapWithKeys(fn (Role $role) => [$role->name => $role]);

        $this->cacheService->putAllRoles($roles);

        return $roles;
    }

    /**
     * Find a role by its name.
     */
    public function findByName(string $roleName): ?Role
    {
        return $this->all()->get($roleName);
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
            $this->cacheService->invalidateCacheForAllLinks();
        }

        return $role->fresh();
    }

    /**
     * Update an existing role name.
     *
     * @param  Role  $role
     */
    public function updateName($role, string $newRoleName): Role
    {
        if ($role->update(['name' => $newRoleName])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Grant a role to all models that are not explicitly denying it.
     *
     * @param  Role  $role
     */
    public function grantByDefault($role): Role
    {
        if ($role->update(['grant_by_default' => true])) {
            $this->cacheService->clear();
        }

        return $role;
    }

    /**
     * Revoke a role's default grant.
     *
     * @param  Role  $role
     */
    public function revokeDefaultGrant($role): Role
    {
        if ($role->update(['grant_by_default' => false])) {
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
     * Get all roles assigned to a specific model.
     *
     * @return Collection<string, Role>
     */
    public function assignedToModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => ! $link['denied'])
            ->map(fn (array $link) => $link['role']);
    }

    /**
     * Get all roles denied from a specific model.
     *
     * @return Collection<string, Role>
     */
    public function deniedFromModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => $link['denied'])
            ->map(fn (array $link) => $link['role']);
    }

    /**
     * Get a page of roles.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Role::query()->whereLike('name', "%{$packet->searchTerm}%");

        $query = match ($packet->prioritizedAttribute) {
            'name' => $query
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            'grant_by_default' => $query
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder),
            'is_active' => $query
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            default => $query,
        };

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Get all roles for a specific model.
     *
     * @return Collection<string, array{role: Role, denied: bool}>
     */
    private function forModel(Model $model): Collection
    {
        return $this->linksForModel($model)
            ->mapWithKeys(function (array $link) {
                [$name, $denied] = [$link['name'], $link['denied']];

                return [
                    $name => [
                        'role' => $this->findByName($name),
                        'denied' => $denied,
                    ],
                ];
            });
    }

    /**
     * Get all role links for a specific model.
     *
     * @return Collection<int, array{name: string, denied: bool}>
     */
    private function linksForModel(Model $model): Collection
    {
        $allRoleLinks = $this->cacheService->getModelRoleLinks($model);

        if ($allRoleLinks) {
            return $allRoleLinks;
        }

        if (! $this->modelInteractsWithRoles($model)) {
            return collect();
        }

        $allRoleLinks = $model->roles()
            ->select([
                'name' => (new Role)->qualifyColumn('name'),
                'denied' => (new ModelHasRole)->qualifyColumn('denied'),
            ])
            ->whereNull((new ModelHasRole)->qualifyColumn('deleted_at'))
            ->get(['name', 'denied'])
            ->map(fn (Role $role) => $role->only(['name', 'denied']));

        $this->cacheService->putModelRoleLinks($model, $allRoleLinks);

        return $allRoleLinks;
    }
}
