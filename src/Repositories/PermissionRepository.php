<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionNotFoundException;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        return Schema::hasTable((new Permission)->getTable());
    }

    /**
     * Check if a permission with the given name exists.
     */
    public function exists(string $permissionName): bool
    {
        return Permission::query()->where('name', $permissionName)->exists();
    }

    /**
     * Get all permissions.
     *
     * @return Collection<string, Permission>
     */
    public function all(): Collection
    {
        $permissions = $this->cacheService->getAllPermissions();

        if ($permissions) {
            return $permissions;
        }

        $permissions = Permission::all()->mapWithKeys(fn (Permission $permission) => [$permission->name => $permission]);

        $this->cacheService->putAllPermissions($permissions);

        return $permissions;
    }

    /**
     * Find a permission by its name.
     */
    public function findByName(string $permissionName): ?Permission
    {
        return $this->all()->get($permissionName);
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
     * Update an existing permission name.
     *
     * @param  Permission  $permission
     */
    public function updateName($permission, string $newPermissionName): Permission
    {
        if ($permission->update(['name' => $newPermissionName])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Grant a permission to all models that are not explicitly denying it.
     *
     * @param  Permission  $permission
     */
    public function grantByDefault($permission): Permission
    {
        if ($permission->update(['grant_by_default' => true])) {
            $this->cacheService->clear();
        }

        return $permission;
    }

    /**
     * Revoke a permission's default grant.
     *
     * @param  Permission  $permission
     */
    public function revokeDefaultGrant($permission): Permission
    {
        if ($permission->update(['grant_by_default' => false])) {
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
     * Get all permissions assigned to a specific model.
     *
     * @return Collection<string, Permission>
     */
    public function assignedToModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => ! $link['denied'])
            ->map(fn (array $link) => $link['permission']);
    }

    /**
     * Get all permissions denied from a specific model.
     *
     * @return Collection<string, Permission>
     */
    public function deniedFromModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => $link['denied'])
            ->map(fn (array $link) => $link['permission']);
    }

    /**
     * Get a page of permissions.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Permission::query()->whereLike('name', "%{$packet->searchTerm}%");

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
     * Get all permissions for a specific model.
     *
     * @return Collection<string, array{permission: Permission, denied: bool}>
     */
    private function forModel(Model $model): Collection
    {
        return $this->linksForModel($model)
            ->mapWithKeys(function (array $link) {
                [$name, $denied] = [$link['name'], $link['denied']];

                return [
                    $name => [
                        'permission' => $this->findByName($name),
                        'denied' => $denied,
                    ],
                ];
            });
    }

    /**
     * Get all permission links for a specific model.
     *
     * @return Collection<int, array{name: string, denied: bool}>
     */
    private function linksForModel(Model $model): Collection
    {
        $allPermissionLinks = $this->cacheService->getModelPermissionLinks($model);

        if ($allPermissionLinks) {
            return $allPermissionLinks;
        }

        if (! $this->modelInteractsWithPermissions($model)) {
            return collect();
        }

        $allPermissionLinks = $model->permissions()
            ->select([
                'name' => (new Permission)->qualifyColumn('name'),
                'denied' => (new ModelHasPermission)->qualifyColumn('denied'),
            ])
            ->whereNull((new ModelHasPermission)->qualifyColumn('deleted_at'))
            ->get(['name', 'denied'])
            ->map(fn (Permission $permission) => $permission->only(['name', 'denied']));

        $this->cacheService->putModelPermissionLinks($model, $allPermissionLinks);

        return $allPermissionLinks;
    }
}
