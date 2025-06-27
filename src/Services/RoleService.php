<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Dtos\AuditLog\AssignRoleAuditLogDto;
use Braxey\Gatekeeper\Dtos\AuditLog\CreateRoleAuditLogDto;
use Braxey\Gatekeeper\Dtos\AuditLog\RevokeRoleAuditLogDto;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\AuditLogRepository;
use Braxey\Gatekeeper\Repositories\ModelHasRoleRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RoleService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    public function create(string $roleName): Role
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();

        $role = $this->roleRepository->create($roleName);

        if (Config::get('gatekeeper.features.audit')) {
            $this->auditLogRepository->create(new CreateRoleAuditLogDto($role));
        }

        return $role;
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

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findByName($roleName);

        // If the model already has this role directly assigned, we don't need to sync again.
        if ($this->modelDirectlyHasRole($model, $role)) {
            return true;
        }

        // Insert the role assignment.
        $this->modelHasRoleRepository->create($model, $role);

        // Audit log the role assignment if auditing is enabled.
        if (Config::get('gatekeeper.features.audit')) {
            $this->auditLogRepository->create(new AssignRoleAuditLogDto($model, $role));
        }

        // Invalidate the roles cache for the model.
        $this->roleRepository->invalidateCacheForModel($model);

        return true;
    }

    /**
     * Assign multiple roles to a model.
     */
    public function assignMultipleToModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($roles) as $roleName) {
            $result = $result && $this->assignToModel($model, $roleName);
        }

        return $result;
    }

    /**
     * Revoke a role from a model.
     */
    public function revokeFromModel(Model $model, Role|string $role): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceRolesFeature();
        $this->enforceRoleInteraction($model);

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findByName($roleName);

        if ($this->modelHasRoleRepository->deleteForModelAndRole($model, $role)) {
            // Audit log the role revocation if auditing is enabled.
            if (Config::get('gatekeeper.features.audit')) {
                $this->auditLogRepository->create(new RevokeRoleAuditLogDto($model, $role));
            }

            // Invalidate the roles cache for the model.
            $this->roleRepository->invalidateCacheForModel($model);

            return true;
        }

        return false;
    }

    /**
     * Revoke multiple roles from a model.
     */
    public function revokeMultipleFromModel(Model $model, array|Arrayable $roles): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($roles) as $roleName) {
            $result = $result && $this->revokeFromModel($model, $roleName);
        }

        return $result;
    }

    /**
     * Check if a model has a given role.
     */
    public function modelHas(Model $model, Role|string $role): bool
    {
        $this->enforceRolesFeature();
        $this->enforceRoleInteraction($model);

        $roleName = $this->resolveEntityName($role);
        $role = $this->roleRepository->findByName($roleName);

        // If the role is not active, we can immediately return false.
        if (! $role->is_active) {
            return false;
        }

        // If the role is currently directly assigned to the model, return true.
        if ($this->modelDirectlyHasRole($model, $role)) {
            return true;
        }

        // If teams are enabled, check if the model has the role through teams.
        if (Config::get('gatekeeper.features.teams')) {
            $onTeamWithRole = $this->teamRepository
                ->getActiveForModel($model)
                ->some(fn (Team $team) => $team->hasRole($role));

            if ($onTeamWithRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model has any of the given roles.
     */
    public function modelHasAny(Model $model, array|Arrayable $roles): bool
    {
        foreach ($this->entityNamesArray($roles) as $roleName) {
            if ($this->modelHas($model, $roleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model has all of the given roles.
     */
    public function modelHasAll(Model $model, array|Arrayable $roles): bool
    {
        foreach ($this->entityNamesArray($roles) as $roleName) {
            if (! $this->modelHas($model, $roleName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a model has a role directly assigned.
     */
    private function modelDirectlyHasRole(Model $model, Role $role): bool
    {
        // Check if the model has the role directly assigned.
        $recentRoleAssignment = $this->modelHasRoleRepository->getRecentForModelAndRoleIncludingTrashed($model, $role);

        // If the role is currently directly assigned to the model, return true.
        return $recentRoleAssignment && ! $recentRoleAssignment->deleted_at;
    }
}
