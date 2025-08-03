<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Enums\PermissionSourceType;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Permission\UnassigningGatekeeperDashboardPermissionFromSelfException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\AssignPermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\CreatePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\DeactivatePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\DeletePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\DenyPermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\GrantedPermissionByDefaultAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\ReactivatePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\RevokedPermissionDefaultGrantAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\UnassignPermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\UndenyPermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\UpdatePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\UpdatePermissionPacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Permission, PermissionPacket>
 */
class PermissionService extends AbstractBaseEntityService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly RoleRepository $roleRepository,
        private readonly FeatureRepository $featureRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly AuditLogRepository $auditLogRepository,
        private readonly CacheService $cacheService,
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
            $this->auditLogRepository->create(CreatePermissionAuditLogPacket::make($createdPermission));
        }

        return $createdPermission->toPacket();
    }

    /**
     * Update an existing permission.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $entity
     * @param UpdatePermissionPacket
     */
    public function update($entity, $packet): PermissionPacket
    {
        return match ($packet->action) {
            EntityUpdateAction::Name->value => $this->updateName($entity, $packet->value),
            EntityUpdateAction::Status->value => $packet->value ? $this->reactivate($entity) : $this->deactivate($entity),
            EntityUpdateAction::DefaultGrant->value => $packet->value ? $this->grantByDefault($entity) : $this->revokeDefaultGrant($entity),
            default => throw new InvalidArgumentException('Invalid update action.'),
        };
    }

    /**
     * Update an existing permission name.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function updateName($permission, string|UnitEnum $newPermissionName): PermissionPacket
    {
        $this->enforceAuditFeature();

        $newPermissionName = $this->resolveEntityName($newPermissionName);

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if ($this->exists($newPermissionName) && $currentPermission->name !== $newPermissionName) {
            throw new PermissionAlreadyExistsException($newPermissionName);
        }

        $oldPermissionName = $currentPermission->name;
        $updatedPermission = $this->permissionRepository->updateName($currentPermission, $newPermissionName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdatePermissionAuditLogPacket::make($updatedPermission, $oldPermissionName));
        }

        return $updatedPermission->toPacket();
    }

    /**
     * Grant a permission to all models that are not explicitly denying it.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function grantByDefault($permission): PermissionPacket
    {
        $this->enforceAuditFeature();

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if ($currentPermission->grant_by_default) {
            return $currentPermission->toPacket();
        }

        $defaultedOnPermission = $this->permissionRepository->grantByDefault($currentPermission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(GrantedPermissionByDefaultAuditLogPacket::make($defaultedOnPermission));
        }

        return $defaultedOnPermission->toPacket();
    }

    /**
     * Revoke a permission's default grant.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function revokeDefaultGrant($permission): PermissionPacket
    {
        $this->enforceAuditFeature();

        $currentPermission = $this->resolveEntity($permission, orFail: true);

        if (! $currentPermission->grant_by_default) {
            return $currentPermission->toPacket();
        }

        $defaultedOffPermission = $this->permissionRepository->revokeDefaultGrant($currentPermission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokedPermissionDefaultGrantAuditLogPacket::make($defaultedOffPermission));
        }

        return $defaultedOffPermission->toPacket();
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
            $this->auditLogRepository->create(DeactivatePermissionAuditLogPacket::make($deactivatedPermission));
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
            $this->auditLogRepository->create(ReactivatePermissionAuditLogPacket::make($reactivatedPermission));
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
            $this->auditLogRepository->create(DeletePermissionAuditLogPacket::make($permission));
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

        $this->modelHasPermissionRepository->assignToModel($model, $permission);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(AssignPermissionAuditLogPacket::make($model, $permission));
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
     * Unassign a permission from a model.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function unassignFromModel(Model $model, $permission): bool
    {
        $this->enforceAuditFeature();

        $permission = $this->resolveEntity($permission, orFail: true);

        // Don't allow an authenticated user to unassign a Gatekeeper dashboard permission from themself.
        $user = Auth::user();

        if ($user instanceof Model && $user->is($model) && in_array($permission->name, [GatekeeperPermission::View->value, GatekeeperPermission::Manage->value])) {
            throw new UnassigningGatekeeperDashboardPermissionFromSelfException;
        }

        $unassigned = $this->modelHasPermissionRepository->unassignFromModel($model, $permission);

        if ($unassigned && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UnassignPermissionAuditLogPacket::make($model, $permission));
        }

        return $unassigned;
    }

    /**
     * Unassign multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function unassignAllFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->resolveEntities($permissions, orFail: true)->each(function (Permission $permission) use ($model, &$result) {
            $result = $result && $this->unassignFromModel($model, $permission);
        });

        return $result;
    }

    /**
     * Deny a permission from a model.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function denyFromModel(Model $model, $permission): bool
    {
        $this->enforceAuditFeature();

        $permission = $this->resolveEntity($permission, orFail: true);

        $denied = $this->modelHasPermissionRepository->denyFromModel($model, $permission);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DenyPermissionAuditLogPacket::make($model, $permission));
        }

        return (bool) $denied;
    }

    /**
     * Deny multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function denyAllFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->resolveEntities($permissions, orFail: true)->each(function (Permission $permission) use ($model, &$result) {
            $result = $result && $this->denyFromModel($model, $permission);
        });

        return $result;
    }

    /**
     * Undeny a permission from a model.
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function undenyFromModel(Model $model, $permission): bool
    {
        $this->enforceAuditFeature();

        $permission = $this->resolveEntity($permission, orFail: true);

        $denied = $this->modelHasPermissionRepository->undenyFromModel($model, $permission);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UndenyPermissionAuditLogPacket::make($model, $permission));
        }

        return (bool) $denied;
    }

    /**
     * Undeny multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function undenyAllFromModel(Model $model, array|Arrayable $permissions): bool
    {
        $result = true;

        $this->resolveEntities($permissions, orFail: true)->each(function (Permission $permission) use ($model, &$result) {
            $result = $result && $this->undenyFromModel($model, $permission);
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
        // If the model is not using the HasPermissions trait, return false.
        if (! $this->modelInteractsWithPermissions($model)) {
            return false;
        }

        $permission = $this->resolveEntity($permission);

        // If the permission does not exist or is inactive, return false.
        if (! $permission || ! $permission->is_active) {
            return false;
        }

        // If the model permission access is cached, return it.
        $modelPermissionAccess = $this->cacheService->getModelPermissionAccess($model) ?: collect();

        if ($modelPermissionAccess->has($permission->name)) {
            return $modelPermissionAccess->get($permission->name);
        }

        $has = $this->determineModelHas($model, $permission);

        // Cache then return the result.
        $this->cacheService->putModelPermissionAccess($model,
            $modelPermissionAccess->put($permission->name, $has)
        );

        return $has;
    }

    /**
     * Check if a model directly has the given permission (not granted through roles or teams).
     *
     * @param  Permission|PermissionPacket|string|UnitEnum  $permission
     */
    public function modelHasDirectly(Model $model, $permission): bool
    {
        $permission = $this->resolveEntity($permission);

        if (! $permission || ! $permission->is_active) {
            return false;
        }

        return $this->permissionRepository->assignedToModel($model)->has($permission->name);
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
     * @return Collection<string, PermissionPacket>
     */
    public function getAll(): Collection
    {
        return $this->permissionRepository->all()
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all permissions directly assigned to a model.
     *
     * @return Collection<string, PermissionPacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->permissionRepository->assignedToModel($model)
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<string, PermissionPacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->permissionRepository->all()
            ->filter(fn (Permission $permission) => $this->modelHas($model, $permission))
            ->map(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->modelInteractsWithPermissions($model)) {
            return $result;
        }

        $deniedPermissions = $this->permissionRepository->deniedFromModel($model);
        $activeUndeniedPermissions = $this->permissionRepository->all()
            ->filter(fn (Permission $permission) => ! $deniedPermissions->has($permission->name))
            ->filter(fn (Permission $permission) => $permission->is_active);

        // Permissions granted by default.
        $activeUndeniedPermissions
            ->filter(fn (Permission $permission) => $permission->grant_by_default)
            ->each(function (Permission $permission) use (&$sourcesMap) {
                $sourcesMap[$permission->name][] = [
                    'type' => PermissionSourceType::DEFAULT,
                ];
            });

        // Permissions directly assigned.
        $this->permissionRepository->assignedToModel($model)
            ->filter(fn (Permission $permission) => $permission->is_active)
            ->each(function (Permission $permission) use (&$sourcesMap) {
                $sourcesMap[$permission->name][] = ['type' => PermissionSourceType::DIRECT];
            });

        // Permissions through roles.
        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($model)) {
            $this->roleRepository->all()
                ->filter(fn (Role $role) => $model->hasRole($role))
                ->each(function (Role $role) use (&$sourcesMap, $activeUndeniedPermissions) {
                    $activeUndeniedPermissions
                        ->filter(fn (Permission $permission) => $role->hasPermission($permission))
                        ->each(function (Permission $permission) use (&$sourcesMap, $role) {
                            $sourcesMap[$permission->name][] = [
                                'type' => PermissionSourceType::ROLE,
                                'role' => $role->name,
                            ];
                        });
                });
        }

        // Permissions through features.
        if ($this->featuresFeatureEnabled() && $this->modelInteractsWithFeatures($model)) {
            $this->featureRepository->all()
                ->filter(fn (Feature $feature) => $model->hasFeature($feature))
                ->each(function (Feature $feature) use (&$sourcesMap, $activeUndeniedPermissions) {
                    $activeUndeniedPermissions
                        ->filter(fn (Permission $permission) => $feature->hasPermission($permission))
                        ->each(function (Permission $permission) use (&$sourcesMap, $feature) {
                            $sourcesMap[$permission->name][] = [
                                'type' => PermissionSourceType::FEATURE,
                                'feature' => $feature->name,
                            ];
                        });
                });
        }

        // Permissions through teams.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->each(function (Team $team) use (&$sourcesMap, $activeUndeniedPermissions) {
                    $activeUndeniedPermissions
                        ->filter(fn (Permission $permission) => $team->hasPermission($permission))
                        ->each(function (Permission $permission) use (&$sourcesMap, $team) {
                            $sourcesMap[$permission->name][] = [
                                'type' => PermissionSourceType::TEAM,
                                'team' => $team->name,
                            ];
                        });
                });
        }

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
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        return $this->permissionRepository->getPage($packet)
            ->through(fn (Permission $permission) => $permission->toPacket());
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

    /**
     * Determine if a model has the given permission.
     */
    private function determineModelHas(Model $model, Permission $permission): bool
    {
        // If the permission is denied from the model, return false.
        if ($this->permissionRepository->deniedFromModel($model)->has($permission->name)) {
            return false;
        }

        // If the permission is granted by default, return true.
        if ($permission->grant_by_default) {
            return true;
        }

        // If the permission is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $permission)) {
            return true;
        }

        // If roles are enabled and the model is using the HasRoles trait, check if the model has the permission through a role.
        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($model)) {
            $hasRoleWithPermission = $this->roleRepository->all()
                ->filter(fn (Role $role) => $model->hasRole($role))
                ->some(fn (Role $role) => $role->hasPermission($permission));

            // If the model has any roles with access to the permission, return true.
            if ($hasRoleWithPermission) {
                return true;
            }
        }

        // If features are enabled and the model is using the HasFeatures trait, check if the model has the permission through a feature.
        if ($this->featuresFeatureEnabled() && $this->modelInteractsWithFeatures($model)) {
            $hasFeatureWithPermission = $this->featureRepository->all()
                ->filter(fn (Feature $feature) => $model->hasFeature($feature))
                ->some(fn (Feature $feature) => $feature->hasPermission($permission));

            // If the model has any features with access to the permission, return true.
            if ($hasFeatureWithPermission) {
                return true;
            }
        }

        // If teams are enabled and the model is using the HasTeams trait, check if the model has the permission through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithPermission = $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->some(fn (Team $team) => $team->hasPermission($permission));

            // If the model is on any teams with access to the permission, return true.
            if ($onTeamWithPermission) {
                return true;
            }
        }

        return false;
    }
}
