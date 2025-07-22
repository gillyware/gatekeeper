<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\AssignPermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\CreatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\DeactivatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\DeletePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\ReactivatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\RevokePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\UpdatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Enums\PermissionSourceType;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Permission\RevokingGatekeeperDashboardPermissionFromSelfException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Permission, PermissionPacket>
 */
class PermissionService extends AbstractBaseEntityService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if the permissions table exists.
     */
    public function tableExists(): bool
    {
        return $this->permissionRepository->tableExists();
    }

    /**
     * Check if a permission with the given name exists.
     */
    public function exists(string|UnitEnum $permissionName): bool
    {
        $permissionName = $this->resolveEntityName($permissionName);

        return $this->permissionRepository->exists($permissionName);
    }

    /**
     * Create a new permission.
     */
    public function create(string|UnitEnum $permissionName): PermissionPacket
    {
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permissionName);

        if ($this->exists($permissionName)) {
            throw new PermissionAlreadyExistsException($permissionName);
        }

        $createdPermission = $this->permissionRepository->create($permissionName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new CreatePermissionAuditLogDto($createdPermission));
        }

        return $createdPermission->toPacket();
    }

    /**
     * Update an existing permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function update($permission, string|UnitEnum $newPermissionName): PermissionPacket
    {
        $this->enforceAuditFeature();

        $newPermissionName = $this->resolveEntityName($newPermissionName);

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if ($this->exists($newPermissionName) && $currentPermission->name !== $newPermissionName) {
            throw new PermissionAlreadyExistsException($newPermissionName);
        }

        $oldPermissionName = $currentPermission->name;
        $updatedPermission = $this->permissionRepository->update($currentPermission, $newPermissionName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new UpdatePermissionAuditLogDto($updatedPermission, $oldPermissionName));
        }

        return $updatedPermission->toPacket();
    }

    /**
     * Deactivate a permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function deactivate($permission): PermissionPacket
    {
        $this->enforceAuditFeature();

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if (! $currentPermission->is_active) {
            return $currentPermission->toPacket();
        }

        $deactivatedPermission = $this->permissionRepository->deactivate($currentPermission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeactivatePermissionAuditLogDto($deactivatedPermission));
        }

        return $deactivatedPermission->toPacket();
    }

    /**
     * Reactivate a permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function reactivate($permission): PermissionPacket
    {
        $this->enforceAuditFeature();

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if ($currentPermission->is_active) {
            return $currentPermission->toPacket();
        }

        $reactivatedPermission = $this->permissionRepository->reactivate($currentPermission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new ReactivatePermissionAuditLogDto($reactivatedPermission));
        }

        return $reactivatedPermission->toPacket();
    }

    /**
     * Delete a permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function delete($permission): bool
    {
        $this->enforceAuditFeature();

        $permission = $this->resolveEntity($permission);

        if (! $permission) {
            return true;
        }

        // Delete any existing assignments for the permission being deleted.
        if ($this->modelHasPermissionRepository->existsForEntity($permission)) {
            $this->modelHasPermissionRepository->deleteForEntity($permission);
        }

        $deleted = $this->permissionRepository->delete($permission);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeletePermissionAuditLogDto($permission));
        }

        return (bool) $deleted;
    }

    /**
     * Assign a permission to a model.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function assignToModel(Model $model, $permission): bool
    {
        $this->enforceAuditFeature();
        $this->enforcePermissionInteraction($model);
        $this->enforceModelIsNotPermission($model, 'Permissions cannot be assigned to other permissions');

        $permission = $this->resolveEntity($permission, orFail: true);

        // If the model already has this permission directly assigned, return true.
        if ($this->modelHasDirectly($model, $permission)) {
            return true;
        }

        $this->modelHasPermissionRepository->create($model, $permission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new AssignPermissionAuditLogDto($model, $permission));
        }

        return true;
    }

    /**
     * Assign multiple permissions to a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function assignAllToModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->resolveEntities($permissions, orFail: true)->each(function (Permission $permission) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $permission);
        });

        return $result;
    }

    /**
     * Revoke a permission from a model.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function revokeFromModel(Model $model, $permission): bool
    {
        $this->enforceAuditFeature();

        $permission = $this->resolveEntity($permission, orFail: true);

        // Don't allow an authenticated user to revoke a Gatekeeper dashboard permission from themself.
        if (Auth::user()?->is($model) && in_array($permission->name, [GatekeeperPermission::View->value, GatekeeperPermission::Manage->value])) {
            throw new RevokingGatekeeperDashboardPermissionFromSelfException;
        }

        $revoked = $this->modelHasPermissionRepository->deleteForModelAndEntity($model, $permission);

        if ($revoked && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokePermissionAuditLogDto($model, $permission));
        }

        return $revoked;
    }

    /**
     * Revoke multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->resolveEntities($permissions, orFail: true)->each(function (Permission $permission) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $permission);
        });

        return $result;
    }

    /**
     * Check if a model has the given permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function modelHas(Model $model, $permission): bool
    {
        // To access the permission, the model must be using the roles trait.
        if (! $this->modelInteractsWithPermissions($model)) {
            return false;
        }

        $permission = $this->resolveEntity($permission);

        // The permission cannot be accessed if it does not exist or is inactive.
        if (! $permission || ! $permission->is_active) {
            return false;
        }

        // If the permission is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $permission)) {
            return true;
        }

        // If roles are enabled and the model interacts with roles, check if the model has the permission through a role.
        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($model)) {
            $hasRoleWithPermission = $this->roleRepository
                ->activeForModel($model)
                ->some(fn (Role $role) => $role->hasPermission($permission));

            // If the model has any active roles with the permission, return true.
            if ($hasRoleWithPermission) {
                return true;
            }
        }

        // If teams are enabled and the model interacts with teams, check if the model has the permission through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithPermission = $this->teamRepository
                ->activeForModel($model)
                ->some(fn (Team $team) => $team->hasPermission($permission));

            // If the model has any active teams with the permission, return true.
            if ($onTeamWithPermission) {
                return true;
            }
        }

        // Return false by default.
        return false;
    }

    /**
     * Check if a model directly has the given permission (not granted through roles or teams).
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function modelHasDirectly(Model $model, $permission): bool
    {
        $permission = $this->resolveEntity($permission);

        return $permission && $this->permissionRepository->activeForModel($model)->some(fn (Permission $p) => $permission->name === $p->name);
    }

    /**
     * Check if a model has any of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function modelHasAny(Model $model, array|Arrayable $permissions): bool
    {
        return $this->resolveEntities($permissions)->filter()->some(
            fn (Permission $permission) => $this->modelHas($model, $permission)
        );
    }

    /**
     * Check if a model has all of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function modelHasAll(Model $model, array|Arrayable $permissions): bool
    {
        return $this->resolveEntities($permissions)->every(
            fn (?Permission $permission) => $permission && $this->modelHas($model, $permission)
        );
    }

    /**
     * Find a permission by its name.
     */
    public function findByName(string|UnitEnum $permissionName): ?PermissionPacket
    {
        return $this->resolveEntity($permissionName)?->toPacket();
    }

    /**
     * Get all permissions.
     *
     * @return Collection<PermissionPacket>
     */
    public function getAll(): Collection
    {
        return $this->permissionRepository->all()
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<PermissionPacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->permissionRepository->all()
            ->filter(fn (Permission $permission) => $this->modelHas($model, $permission))
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all permissions directly assigned to a model.
     *
     * @return Collection<PermissionPacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->permissionRepository->forModel($model)
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $sourcesMap = [];

        if ($this->modelInteractsWithPermissions($model)) {
            $this->permissionRepository->activeForModel($model)
                ->each(function (Permission $permission) use (&$sourcesMap) {
                    $sourcesMap[$permission->name][] = ['type' => PermissionSourceType::DIRECT];
                });
        }

        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($model)) {
            $this->roleRepository->activeForModel($model)
                ->each(function (Role $role) use (&$sourcesMap) {
                    $this->permissionRepository->activeForModel($role)
                        ->each(function (Permission $permission) use (&$sourcesMap, $role) {
                            $sourcesMap[$permission->name][] = [
                                'type' => PermissionSourceType::ROLE,
                                'role' => $role->name,
                            ];
                        });
                });
        }

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $teams = $this->teamRepository->activeForModel($model)
                ->each(function (Team $team) use (&$sourcesMap) {
                    $this->permissionRepository->activeForModel($team)
                        ->each(function (Permission $permission) use (&$sourcesMap, $team) {
                            $sourcesMap[$permission->name][] = [
                                'type' => PermissionSourceType::TEAM,
                                'team' => $team->name,
                            ];
                        });

                    if ($this->rolesFeatureEnabled()) {
                        $this->roleRepository->activeForModel($team)
                            ->each(function (Role $role) use (&$sourcesMap, $team) {
                                $this->permissionRepository->activeForModel($role)
                                    ->each(function (Permission $permission) use (&$sourcesMap, $role, $team) {
                                        $sourcesMap[$permission->name][] = [
                                            'type' => PermissionSourceType::TEAM_ROLE,
                                            'team' => $team->name,
                                            'role' => $role->name,
                                        ];
                                    });
                            });
                    }
                });
        }

        $result = collect();

        foreach ($sourcesMap as $permissionName => $sources) {
            $result->push([
                'name' => $permissionName,
                'sources' => $sources,
            ]);
        }

        return $result;
    }

    /**
     * Get a page of permissions.
     */
    public function getPage(EntityPagePacket $entityPagePacket): LengthAwarePaginator
    {
        return $this->permissionRepository->getPage($entityPagePacket);
    }

    /**
     * Get the permission model from the permission or permission name.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    protected function resolveEntity($permission, bool $orFail = false): ?Permission
    {
        if ($permission instanceof Permission) {
            return $permission;
        }

        $permissionName = $this->resolveEntityName($permission);

        return $orFail
            ? $this->permissionRepository->findOrFailByName($permissionName)
            : $this->permissionRepository->findByName($permissionName);
    }
}
