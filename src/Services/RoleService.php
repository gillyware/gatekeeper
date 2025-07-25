<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Enums\RoleSourceType;
use Gillyware\Gatekeeper\Exceptions\Role\RoleAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\AssignRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\CreateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\DeactivateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\DeleteRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\ReactivateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\RevokeRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Role\UpdateRoleAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
     */
    public function update($role, string|UnitEnum $newRoleName): RolePacket
    {
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $newRoleName = $this->resolveEntityName($newRoleName);

        $currentRole = $this->resolveEntity($role, orFail: true);

        if ($this->exists($newRoleName) && $currentRole->name !== $newRoleName) {
            throw new RoleAlreadyExistsException($newRoleName);
        }

        $oldRoleName = $currentRole->name;
        $updatedRole = $this->roleRepository->update($currentRole, $newRoleName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdateRoleAuditLogPacket::make($updatedRole, $oldRoleName));
        }

        return $updatedRole->toPacket();
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

        $this->modelHasRoleRepository->create($model, $role);

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
     * Revoke a role from a model.
     *
     * @param  Role|RolePacket|string|UnitEnum  $role
     */
    public function revokeFromModel(Model $model, $role): bool
    {
        $this->enforceAuditFeature();

        $role = $this->resolveEntity($role, orFail: true);

        $revoked = $this->modelHasRoleRepository->deleteForModelAndEntity($model, $role);

        if ($revoked && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokeRoleAuditLogPacket::make($model, $role));
        }

        return $revoked;
    }

    /**
     * Revoke multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->resolveEntities($roles, orFail: true)->each(function (Role $role) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $role);
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
        // To access the role, the roles feature must be enabled and the model must be using the roles trait.
        if (! $this->rolesFeatureEnabled() || ! $this->modelInteractsWithRoles($model)) {
            return false;
        }

        $role = $this->resolveEntity($role);

        // The role cannot be accessed if it does not exist or is inactive.
        if (! $role || ! $role->is_active) {
            return false;
        }

        // If the role is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $role)) {
            return true;
        }

        // If teams are enabled and the model interacts with teams, check if the model has the role through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithRole = $this->teamRepository
                ->activeForModel($model)
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

        return $role && $this->roleRepository->activeForModel($model)->some(fn (Role $r) => $role->name === $r->name);
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
     * @return Collection<RolePacket>
     */
    public function getAll(): Collection
    {
        return $this->roleRepository->all()
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<RolePacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->roleRepository->all()
            ->filter(fn (Role $role) => $this->modelHas($model, $role))
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all roles directly assigned to a model.
     *
     * @return Collection<RolePacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->roleRepository->forModel($model)
            ->map(fn (Role $role) => $role->toPacket());
    }

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->rolesFeatureEnabled()) {
            return $result;
        }

        if ($this->modelInteractsWithRoles($model)) {
            $this->roleRepository->activeForModel($model)
                ->each(function (Role $role) use (&$sourcesMap) {
                    $sourcesMap[$role->name][] = ['type' => RoleSourceType::DIRECT];
                });
        }

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $this->teamRepository->activeForModel($model)
                ->each(function (Team $team) use (&$sourcesMap) {
                    $this->roleRepository->activeForModel($team)
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
