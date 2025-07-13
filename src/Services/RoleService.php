<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Dtos\AuditLog\Role\AssignRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\CreateRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\DeactivateRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\DeleteRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\ReactivateRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\RevokeRoleAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Role\UpdateRoleAuditLogDto;
use Gillyware\Gatekeeper\Exceptions\Role\DeletingAssignedRoleException;
use Gillyware\Gatekeeper\Exceptions\Role\RoleAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RoleService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if a role with the given name exists.
     */
    public function exists(string $roleName): bool
    {
        return $this->roleRepository->exists($roleName);
    }

    /**
     * Create a new role.
     */
    public function create(string $roleName): Role
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        if ($this->exists($roleName)) {
            throw new RoleAlreadyExistsException($roleName);
        }

        $role = $this->roleRepository->create($roleName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new CreateRoleAuditLogDto($role));
        }

        return $role;
    }

    /**
     * Update an existing role.
     */
    public function update(Role $role, string $roleName): Role
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        if ($this->exists($roleName) && $role->name !== $roleName) {
            throw new RoleAlreadyExistsException($roleName);
        }

        $oldRoleName = $role->name;
        $role = $this->roleRepository->update($role, $roleName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new UpdateRoleAuditLogDto($role, $oldRoleName));
        }

        return $role;
    }

    /**
     * Deactivate a role.
     */
    public function deactivate(Role $role): Role
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        if (! $role->is_active) {
            return $role;
        }

        $role = $this->roleRepository->deactivate($role);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeactivateRoleAuditLogDto($role));
        }

        return $role;
    }

    /**
     * Reactivate a role.
     */
    public function reactivate(Role $role): Role
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        if ($role->is_active) {
            return $role;
        }

        $role = $this->roleRepository->reactivate($role);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new ReactivateRoleAuditLogDto($role));
        }

        return $role;
    }

    /**
     * Delete a role.
     */
    public function delete(Role|string $role): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findByName($roleName);

        if (! $role) {
            return true;
        }

        // If the role is currently assigned to any model, we cannot delete it.
        if ($this->modelHasRoleRepository->existsForRole($role)) {
            throw new DeletingAssignedRoleException($roleName);
        }

        $deleted = $this->roleRepository->delete($role);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeleteRoleAuditLogDto($role));
        }

        return $deleted;
    }

    /**
     * Assign a role to a model.
     */
    public function assignToModel(Model $model, Role|string $role): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();
        $this->enforceRoleInteraction($model);
        $this->enforceModelIsNotRole($model, 'Roles cannot be assigned to other roles');
        $this->enforceModelIsNotPermission($model, 'Roles cannot be assigned to permissions');

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findOrFailByName($roleName);

        // If the model already has this role directly assigned, return true.
        if ($this->modelHasDirectly($model, $role)) {
            return true;
        }

        $this->modelHasRoleRepository->create($model, $role);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new AssignRoleAuditLogDto($model, $role));
        }

        return true;
    }

    /**
     * Assign multiple roles to a model.
     */
    public function assignMultipleToModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->entityNames($roles)->each(function (string $roleName) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $roleName);
        });

        return $result;
    }

    /**
     * Revoke a role from a model.
     */
    public function revokeFromModel(Model $model, Role|string $role): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findOrFailByName($roleName);

        $revoked = $this->modelHasRoleRepository->deleteForModelAndRole($model, $role);

        if ($revoked && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokeRoleAuditLogDto($model, $role));
        }

        return $revoked;
    }

    /**
     * Revoke multiple roles from a model.
     */
    public function revokeMultipleFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        $this->entityNames($roles)->each(function (string $roleName) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $roleName);
        });

        return $result;
    }

    /**
     * Check if a model has a given role.
     */
    public function modelHas(Model $model, Role|string $role): bool
    {
        // To access the role, the roles feature must be enabled and the model must be using the roles trait.
        if (! $this->rolesFeatureEnabled() || ! $this->modelInteractsWithRoles($model)) {
            return false;
        }

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findByName($roleName);

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
     * Check if a model has a role directly assigned, not through teams.
     */
    public function modelHasDirectly(Model $model, Role $role): bool
    {
        return $this->roleRepository->activeForModel($model)->some(fn (Role $r) => $role->is($r));
    }

    /**
     * Check if a model has any of the given roles.
     */
    public function modelHasAny(Model $model, array|Arrayable $roles): bool
    {
        return $this->entityNames($roles)->some(
            fn (string $roleName) => $this->modelHas($model, $roleName)
        );
    }

    /**
     * Check if a model has all of the given roles.
     */
    public function modelHasAll(Model $model, array|Arrayable $roles): bool
    {
        return $this->entityNames($roles)->every(
            fn (string $roleName) => $this->modelHas($model, $roleName)
        );
    }

    /**
     * Find a role by its name.
     */
    public function findByName(string $roleName): ?Role
    {
        return $this->roleRepository->findByName($roleName);
    }

    /**
     * Get all roles.
     */
    public function getAll(): Collection
    {
        return $this->roleRepository->all();
    }

    /**
     * Get all roles directly assigned to a model.
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->roleRepository->forModel($model);
    }

    /**
     * Get all effective roles for a model, including those from teams.
     */
    public function getEffectiveForModel(Model $model): Collection
    {
        return $this->roleRepository->all()
            ->filter(fn (Role $role) => $this->modelHas($model, $role));
    }
}
