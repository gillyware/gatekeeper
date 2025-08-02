<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\RoleSourceType;
use Gillyware\Gatekeeper\Exceptions\Role\RoleAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\AssignRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\CreateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\DeactivateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\DeleteRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\DenyRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\GrantedRoleByDefaultAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\ReactivateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\RevokedRoleDefaultGrantAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\UnassignRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\UndenyRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\UpdateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\UpdateRolePacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Role, RolePacket>
 */
class RoleService extends AbstractBaseEntityService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if the roles table exists.
     */
    public function tableExists(): bool
    {
        return $this->roleRepository->tableExists();
    }

    /**
     * Check if a role with the given name exists.
     */
    public function exists(string|UnitEnum $roleName): bool
    {
        $roleName = $this->resolveEntityName($roleName);

        return $this->roleRepository->exists($roleName);
    }

    /**
     * Create a new role.
     */
    public function create(string|UnitEnum $roleName): RolePacket
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $roleName = $this->resolveEntityName($roleName);

        if ($this->exists($roleName)) {
            throw new RoleAlreadyExistsException($roleName);
        }

        $createdRole = $this->roleRepository->create($roleName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(CreateRoleAuditLogPacket::make($createdRole));
        }

        return $createdRole->toPacket();
    }

    /**
     * Update an existing role.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     * @param UpdateRolePacket
     */
    public function update($role, $packet): RolePacket
    {
        return match ($packet->action) {
            EntityUpdateAction::Name->value => $this->updateName($role, $packet->value),
            EntityUpdateAction::Status->value => $packet->value ? $this->reactivate($role) : $this->deactivate($role),
            EntityUpdateAction::DefaultGrant->value => $packet->value ? $this->grantByDefault($role) : $this->revokeDefaultGrant($role),
            default => throw new InvalidArgumentException('Invalid update action.'),
        };
    }

    /**
     * Update an existing role name.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function updateName($role, string|UnitEnum $newRoleName): RolePacket
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $newRoleName = $this->resolveEntityName($newRoleName);

        $currentRole = $this->resolveEntity($role, orFail: true);

        if ($this->exists($newRoleName) && $currentRole->name !== $newRoleName) {
            throw new RoleAlreadyExistsException($newRoleName);
        }

        $oldRoleName = $currentRole->name;
        $updatedRole = $this->roleRepository->updateName($currentRole, $newRoleName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdateRoleAuditLogPacket::make($updatedRole, $oldRoleName));
        }

        return $updatedRole->toPacket();
    }

    /**
     * Grant a role to all models that are not explicitly denying it.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function grantByDefault($role): RolePacket
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $currentRole = $this->resolveEntity($role, orFail: true);

        if ($currentRole->grant_by_default) {
            return $currentRole->toPacket();
        }

        $defaultedOnRole = $this->roleRepository->grantByDefault($currentRole);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(GrantedRoleByDefaultAuditLogPacket::make($defaultedOnRole));
        }

        return $defaultedOnRole->toPacket();
    }

    /**
     * Revoke a role's default grant.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function revokeDefaultGrant($role): RolePacket
    {
        $this->enforceAuditFeature();

        $currentRole = $this->resolveEntity($role, orFail: true);

        if (! $currentRole->grant_by_default) {
            return $currentRole->toPacket();
        }

        $defaultedOffRole = $this->roleRepository->revokeDefaultGrant($currentRole);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokedRoleDefaultGrantAuditLogPacket::make($defaultedOffRole));
        }

        return $defaultedOffRole->toPacket();
    }

    /**
     * Deactivate a role.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function deactivate($role): RolePacket
    {
        $this->enforceAuditFeature();

        $currentRole = $this->resolveEntity($role, orFail: true);

        if (! $currentRole->is_active) {
            return $currentRole->toPacket();
        }

        $deactivatedRole = $this->roleRepository->deactivate($currentRole);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeactivateRoleAuditLogPacket::make($deactivatedRole));
        }

        return $deactivatedRole->toPacket();
    }

    /**
     * Reactivate a role.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function reactivate($role): RolePacket
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $currentRole = $this->resolveEntity($role, orFail: true);

        if ($currentRole->is_active) {
            return $currentRole->toPacket();
        }

        $reactivatedRole = $this->roleRepository->reactivate($currentRole);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(ReactivateRoleAuditLogPacket::make($reactivatedRole));
        }

        return $reactivatedRole->toPacket();
    }

    /**
     * Delete a role.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function delete($role): bool
    {
        $this->enforceAuditFeature();

        $role = $this->resolveEntity($role);

        if (! $role) {
            return true;
        }

        // Delete any existing assignments for the role being deleted.
        if ($this->modelHasRoleRepository->existsForEntity($role)) {
            $this->modelHasRoleRepository->deleteForEntity($role);
        }

        $deleted = $this->roleRepository->delete($role);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeleteRoleAuditLogPacket::make($role));
        }

        return (bool) $deleted;
    }

    /**
     * Assign a role to a model.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function assignToModel(Model $model, $role): bool
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();
        $this->enforceRoleInteraction($model);
        $this->enforceModelIsNotRole($model, 'Roles cannot be assigned to other roles');
        $this->enforceModelIsNotPermission($model, 'Roles cannot be assigned to permissions');

        $role = $this->resolveEntity($role, orFail: true);

        // If the model already has this role directly assigned, return true.
        if ($this->modelHasDirectly($model, $role)) {
            return true;
        }

        $this->modelHasRoleRepository->assignToModel($model, $role);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(AssignRoleAuditLogPacket::make($model, $role));
        }

        return true;
    }

    /**
     * Assign multiple roles to a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function assignAllToModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->resolveEntities($roles, orFail: true)->each(function (Role $role) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $role);
        });

        return $result;
    }

    /**
     * Unassign a role from a model.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function unassignFromModel(Model $model, $role): bool
    {
        $this->enforceAuditFeature();

        $role = $this->resolveEntity($role, orFail: true);

        $unassigned = $this->modelHasRoleRepository->unassignFromModel($model, $role);

        if ($unassigned && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UnassignRoleAuditLogPacket::make($model, $role));
        }

        return $unassigned;
    }

    /**
     * Unassign multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function unassignAllFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->resolveEntities($roles, orFail: true)->each(function (Role $role) use ($model, &$result) {
            $result = $result && $this->unassignFromModel($model, $role);
        });

        return $result;
    }

    /**
     * Deny a role from a model.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function denyFromModel(Model $model, $role): bool
    {
        $this->enforceAuditFeature();

        $role = $this->resolveEntity($role, orFail: true);

        $denied = $this->modelHasRoleRepository->denyFromModel($model, $role);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DenyRoleAuditLogPacket::make($model, $role));
        }

        return (bool) $denied;
    }

    /**
     * Deny multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $features
     */
    public function denyAllFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->resolveEntities($roles, orFail: true)->each(function (Role $role) use ($model, &$result) {
            $result = $result && $this->denyFromModel($model, $role);
        });

        return $result;
    }

    /**
     * Undeny a role from a model.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function undenyFromModel(Model $model, $role): bool
    {
        $this->enforceRolesFeature();
        $this->enforceAuditFeature();

        $role = $this->resolveEntity($role, orFail: true);

        $denied = $this->modelHasRoleRepository->undenyFromModel($model, $role);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UndenyRoleAuditLogPacket::make($model, $role));
        }

        return (bool) $denied;
    }

    /**
     * Undeny multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $features
     */
    public function undenyAllFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->resolveEntities($roles, orFail: true)->each(function (Role $role) use ($model, &$result) {
            $result = $result && $this->undenyFromModel($model, $role);
        });

        return $result;
    }

    /**
     * Check if a model has the given role.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function modelHas(Model $model, $role): bool
    {
        // If the roles feature is disabled or the model is not using the HasRoles trait, return false.
        if (! $this->rolesFeatureEnabled() || ! $this->modelInteractsWithRoles($model)) {
            return false;
        }

        $role = $this->resolveEntity($role);

        // If the role does not exist or is inactive, return false.
        if (! $role || ! $role->is_active) {
            return false;
        }

        // If the role is denied from the model, return false.
        if ($this->roleRepository->deniedFromModel($model)->has($role->name)) {
            return false;
        }

        // If the role is granted by default, return true.
        if ($role->grant_by_default) {
            return true;
        }

        // If the role is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $role)) {
            return true;
        }

        // If teams are enabled and the model is using the HasTeams trait, check if the model has the role through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithRole = $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->some(fn (Team $team) => $team->hasRole($role));

            if ($onTeamWithRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model directly has the given role (not granted through teams).
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function modelHasDirectly(Model $model, $role): bool
    {
        $role = $this->resolveEntity($role);

        if (! $role) {
            return false;
        }

        $foundAssignment = $this->roleRepository->assignedToModel($model)->get($role->name);

        return $foundAssignment && $foundAssignment->is_active;
    }

    /**
     * Check if a model has any of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function modelHasAny(Model $model, array|Arrayable $roles): bool
    {
        return $this->resolveEntities($roles)->filter()->some(
            fn (Role $role) => $this->modelHas($model, $role)
        );
    }

    /**
     * Check if a model has all of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function modelHasAll(Model $model, array|Arrayable $roles): bool
    {
        return $this->resolveEntities($roles)->every(
            fn (?Role $role) => $role && $this->modelHas($model, $role)
        );
    }

    /**
     * Find a role by its name.
     */
    public function findByName(string|UnitEnum $roleName): ?RolePacket
    {
        return $this->resolveEntity($roleName)?->toPacket();
    }

    /**
     * Get all roles.
     *
     * @return Collection<string, RolePacket>
     */
    public function getAll(): Collection
    {
        return $this->roleRepository->all()
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all roles directly assigned to a model.
     *
     * @return Collection<string, RolePacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->roleRepository->assignedToModel($model)
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<string, RolePacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->roleRepository->all()
            ->filter(fn (Role $role) => $this->modelHas($model, $role))
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->rolesFeatureEnabled() || ! $this->modelInteractsWithRoles($model)) {
            return $result;
        }

        $deniedRoles = $this->roleRepository->deniedFromModel($model);
        $activeUndeniedRoles = $this->roleRepository->all()
            ->filter(fn (Role $role) => ! $deniedRoles->has($role->name))
            ->filter(fn (Role $role) => $role->is_active);

        // Roles granted by default.
        $activeUndeniedRoles
            ->filter(fn (Role $role) => $role->grant_by_default)
            ->each(function (Role $role) use (&$sourcesMap) {
                $sourcesMap[$role->name][] = [
                    'type' => RoleSourceType::DEFAULT,
                ];
            });

        // Roles directly assigned.
        $this->roleRepository->assignedToModel($model)
            ->filter(fn (Role $role) => $role->is_active)
            ->each(function (Role $role) use (&$sourcesMap) {
                $sourcesMap[$role->name][] = ['type' => RoleSourceType::DIRECT];
            });

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            // Roles through teams.
            $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->each(function (Team $team) use (&$sourcesMap, $activeUndeniedRoles) {
                    $activeUndeniedRoles
                        ->filter(fn (Role $role) => $team->hasRole($role))
                        ->each(function (Role $role) use (&$sourcesMap, $team) {
                            $sourcesMap[$role->name][] = [
                                'type' => RoleSourceType::TEAM,
                                'team' => $team->name,
                            ];
                        });
                });
        }

        foreach ($sourcesMap as $roleName => $sources) {
            $result->push([
                'name' => $roleName,
                'sources' => $sources,
            ]);
        }

        return $result;
    }

    /**
     * Get a page of roles.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        return $this->roleRepository->getPage($packet)
            ->through(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get the role model from the role or role name.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    protected function resolveEntity($role, bool $orFail = false): ?Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        $roleName = $this->resolveEntityName($role);

        return $orFail
            ? $this->roleRepository->findOrFailByName($roleName)
            : $this->roleRepository->findByName($roleName);
    }
}
