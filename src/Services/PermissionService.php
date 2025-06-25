<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Dtos\AuditLog\AssignPermissionAuditLogDto;
use Braxey\Gatekeeper\Dtos\AuditLog\CreatePermissionAuditLogDto;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\AuditLogRepository;
use Braxey\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PermissionService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    public function create(string $permissionName): Permission
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $permission = $this->permissionRepository->create($permissionName);

        if (Config::get('gatekeeper.features.audit', true)) {
            $this->auditLogRepository->create(new CreatePermissionAuditLogDto($permission));
        }

        return $permission;
    }

    /**
     * Assign a permission to a model.
     */
    public function assignToModel(Model $model, Role|string $permission): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforcePermissionInteraction($model);

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        // If the model already has this permission directly assigned, we don't need to sync again.
        if ($this->modelDirectlyHasPermission($model, $permission)) {
            return true;
        }

        // Insert the permission assignment.
        $this->modelHasPermissionRepository->create($model, $permission);

        // Audit log the permission assignment if auditing is enabled.
        if (Config::get('gatekeeper.features.audit', true)) {
            $this->auditLogRepository->create(new AssignPermissionAuditLogDto($model, $permission));
        }

        // Invalidate the permissions cache for the model.
        $this->permissionRepository->invalidateCacheForModel($model);

        return true;
    }

    /**
     * Assign multiple permissions to a model.
     */
    public function assignMultipleToModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($permissions) as $permissionName) {
            $result = $result && $this->assignToModel($model, $permissionName);
        }

        return $result;
    }

    /**
     * Revoke a permission from a model.
     */
    public function revokeFromModel(Model $model, Permission|string $permission): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforcePermissionInteraction($model);

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        if ($this->modelHasPermissionRepository->deleteForModelAndPermission($model, $permission)) {
            // Invalidate the permissions cache for the model.
            $this->permissionRepository->invalidateCacheForModel($model);

            return true;
        }

        return false;
    }

    /**
     * Revoke multiple permissions from a model.
     */
    public function revokeMultipleFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($permissions) as $permissionName) {
            $result = $result && $this->revokeFromModel($model, $permissionName);
        }

        return $result;
    }

    /**
     * Check if a model has a given permission.
     */
    public function modelHas(Model $model, Permission|string $permission): bool
    {
        $this->enforcePermissionInteraction($model);

        $permissionName = $this->resolveEntityName($permission);
        $permission = $this->permissionRepository->findByName($permissionName);

        // If the permission is not active, we can immediately return false.
        if (! $permission->is_active) {
            return false;
        }

        // Fetch the most recent permission assignment.
        $recentPermissionAssignment = $this->modelHasPermissionRepository->getRecentForModelAndPermissionIncludingTrashed($model, $permission);

        // If we find a direct permission assignment, we can use it to determine if the model has the permission.
        if ($recentPermissionAssignment) {
            return ! $recentPermissionAssignment->deleted_at;
        }

        // If roles are enabled, check if the model has the permission through roles.
        if (config('gatekeeper.features.roles', false)) {
            $hasRoleWithPermission = $this->roleRepository
                ->getActiveForModel($model)
                ->some(fn (Role $role) => $role->hasPermission($permission));

            // If the model has any active roles with the permission, return true.
            if ($hasRoleWithPermission) {
                return true;
            }
        }

        // If teams are enabled, check if the model has the permission through the teams roles or permissions.
        if (config('gatekeeper.features.teams', false)) {
            $onTeamWithPermission = $this->teamRepository
                ->getActiveForModel($model)
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
     * Check if a model has any of the given permissions.
     */
    public function modelHasAny(Model $model, array|Arrayable $permissions): bool
    {
        foreach ($this->entityNamesArray($permissions) as $permissionName) {
            if ($this->modelHas($model, $permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model has all of the given permissions.
     */
    public function modelHasAll(Model $model, array|Arrayable $permissions): bool
    {
        foreach ($this->entityNamesArray($permissions) as $permissionName) {
            if (! $this->modelHas($model, $permissionName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a model has a permission directly assigned.
     */
    private function modelDirectlyHasPermission(Model $model, Permission $permission): bool
    {
        // Check if the model has the permission directly assigned.
        $recentPermissionAssignment = $this->modelHasPermissionRepository->getRecentForModelAndPermissionIncludingTrashed($model, $permission);

        // If the permission is currently directly assigned to the model, return true.
        return $recentPermissionAssignment && ! $recentPermissionAssignment->deleted_at;
    }
}
