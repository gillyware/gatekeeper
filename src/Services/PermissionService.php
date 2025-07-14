<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\AssignPermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\CreatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\DeactivatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\DeletePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\ReactivatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\RevokePermissionAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Permission\UpdatePermissionAuditLogDto;
use Gillyware\Gatekeeper\Exceptions\Permission\DeletingAssignedPermissionException;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PermissionService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if a permission with the given name exists.
     */
    public function exists(string $permissionName): bool
    {
        return $this->permissionRepository->exists($permissionName);
    }

    /**
     * Create a new permission.
     */
    public function create(string $permissionName): Permission
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        if ($this->exists($permissionName)) {
            throw new PermissionAlreadyExistsException($permissionName);
        }

        $permission = $this->permissionRepository->create($permissionName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new CreatePermissionAuditLogDto($permission));
        }

        return $permission;
    }

    /**
     * Update an existing permission.
     */
    public function update(Permission|string $permission, string $newPermissionName): Permission
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        if ($this->exists($newPermissionName) && $permission->name !== $newPermissionName) {
            throw new PermissionAlreadyExistsException($newPermissionName);
        }

        $oldPermissionName = $permission->name;
        $permission = $this->permissionRepository->update($permission, $newPermissionName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new UpdatePermissionAuditLogDto($permission, $oldPermissionName));
        }

        return $permission;
    }

    /**
     * Deactivate a permission.
     */
    public function deactivate(Permission|string $permission): Permission
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        if (! $permission->is_active) {
            return $permission;
        }

        $permission = $this->permissionRepository->deactivate($permission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeactivatePermissionAuditLogDto($permission));
        }

        return $permission;
    }

    /**
     * Reactivate a permission.
     */
    public function reactivate(Permission|string $permission): Permission
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        if ($permission->is_active) {
            return $permission;
        }

        $permission = $this->permissionRepository->reactivate($permission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new ReactivatePermissionAuditLogDto($permission));
        }

        return $permission;
    }

    /**
     * Delete a permission.
     */
    public function delete(Permission|string $permission): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        if (! $permission) {
            return true;
        }

        // If the permission is currently assigned to any model, we cannot delete it.
        if ($this->modelHasPermissionRepository->existsForPermission($permission)) {
            throw new DeletingAssignedPermissionException($permissionName);
        }

        $deleted = $this->permissionRepository->delete($permission);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeletePermissionAuditLogDto($permission));
        }

        return $deleted;
    }

    /**
     * Assign a permission to a model.
     */
    public function assignToModel(Model $model, Permission|string $permission): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforcePermissionInteraction($model);
        $this->enforceModelIsNotPermission($model, 'Permissions cannot be assigned to other permissions');

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findOrFailByName($permissionName);

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
     */
    public function assignMultipleToModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->entityNames($permissions)->each(function (string $permissionName) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $permissionName);
        });

        return $result;
    }

    /**
     * Revoke a permission from a model.
     */
    public function revokeFromModel(Model $model, Permission|string $permission): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findOrFailByName($permissionName);

        $revoked = $this->modelHasPermissionRepository->deleteForModelAndPermission($model, $permission);

        if ($revoked && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokePermissionAuditLogDto($model, $permission));
        }

        return $revoked;
    }

    /**
     * Revoke multiple permissions from a model.
     */
    public function revokeMultipleFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->entityNames($permissions)->each(function (string $permissionName) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $permissionName);
        });

        return $result;
    }

    /**
     * Check if a model has a given permission.
     */
    public function modelHas(Model $model, Permission|string $permission): bool
    {
        // To access the permission, the model must be using the roles trait.
        if (! $this->modelInteractsWithPermissions($model)) {
            return false;
        }

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

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
     * Check if a model has a permission directly assigned, not through roles or teams.
     */
    public function modelHasDirectly(Model $model, Permission $permission): bool
    {
        return $this->permissionRepository->activeForModel($model)->some(fn (Permission $p) => $permission->is($p));
    }

    /**
     * Check if a model has any of the given permissions.
     */
    public function modelHasAny(Model $model, array|Arrayable $permissions): bool
    {
        return $this->entityNames($permissions)->some(
            fn (string $permissionName) => $this->modelHas($model, $permissionName)
        );
    }

    /**
     * Check if a model has all of the given permissions.
     */
    public function modelHasAll(Model $model, array|Arrayable $permissions): bool
    {
        return $this->entityNames($permissions)->every(
            fn (string $permissionName) => $this->modelHas($model, $permissionName)
        );
    }

    /**
     * Find a permission by its name.
     */
    public function findByName(string $permissionName): ?Permission
    {
        return $this->permissionRepository->findByName($permissionName);
    }

    /**
     * Get all permissions.
     */
    public function getAll(): Collection
    {
        return $this->permissionRepository->all();
    }

    /**
     * Get all permissions directly assigned to a model.
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->permissionRepository->forModel($model);
    }

    /**
     * Get all effective permissions for a model, including those from roles and teams.
     */
    public function getEffectiveForModel(Model $model): Collection
    {
        return $this->permissionRepository->all()
            ->filter(fn (Permission $permission) => $this->modelHas($model, $permission));
    }
}
