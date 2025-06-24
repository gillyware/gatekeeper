<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithRolesException;
use Braxey\Gatekeeper\Exceptions\RoleNotFoundException;
use Braxey\Gatekeeper\Exceptions\RolesFeatureDisabledException;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\ModelHasRoleRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Braxey\Gatekeeper\Traits\InteractsWithRoles;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
    ) {}

    public function create(string $roleName): Role
    {
        $this->forceRolesFeature();

        return $this->roleRepository->create($roleName);
    }

    /**
     * Assign a role to a model.
     */
    public function assignToModel(Model $model, Role|string $role): bool
    {
        $this->forceRolesFeature();
        $this->forceRoleInteraction($model);

        $roleName = $this->resolveRoleName($role);
        $role = $this->roleRepository->findByName($roleName);

        // If the model already has this role directly assigned, we don't need to sync again.
        if ($this->modelDirectlyHasRole($model, $role)) {
            return true;
        }

        // Insert the role assignment.
        $this->modelHasRoleRepository->create($model, $role);

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

        foreach ($this->roleNamesArray($roles) as $roleName) {
            $result = $result && $this->assignToModel($model, $roleName);
        }

        return $result;
    }

    /**
     * Revoke a role from a model.
     */
    public function revokeFromModel(Model $model, Role|string $role): bool
    {
        $this->forceRolesFeature();
        $this->forceRoleInteraction($model);

        $roleName = $this->resolveRoleName($role);
        $role = $this->roleRepository->findByName($roleName);

        if ($this->modelHasRoleRepository->deleteForModelAndRole($model, $role)) {
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

        foreach ($this->roleNamesArray($roles) as $roleName) {
            $result = $result && $this->revokeFromModel($model, $roleName);
        }

        return $result;
    }

    /**
     * Check if a model has a given role.
     */
    public function modelHas(Model $model, Role|string $role): bool
    {
        $this->forceRolesFeature();
        $this->forceRoleInteraction($model);

        $roleName = $this->resolveRoleName($role);
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
        if (config('gatekeeper.features.teams', false)) {
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
        foreach ($this->roleNamesArray($roles) as $roleName) {
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
        foreach ($this->roleNamesArray($roles) as $roleName) {
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

    /**
     * Force the model to interact with roles.
     */
    private function forceRoleInteraction(Model $model): void
    {
        if (! in_array(InteractsWithRoles::class, class_uses_recursive($model))) {
            throw new ModelDoesNotInteractWithRolesException($model);
        }
    }

    /**
     * Force the role feature to be enabled.
     */
    private function forceRolesFeature(): void
    {
        if (! config('gatekeeper.features.roles', false)) {
            throw new RolesFeatureDisabledException;
        }
    }

    /**
     * Convert an array or Arrayable object of roles or role names to an array of role names.
     */
    private function roleNamesArray(array|Arrayable $roles): array
    {
        $rolesArray = $roles instanceof Arrayable ? $roles->toArray() : $roles;

        return array_map(function (Role|array|string $role) {
            return $this->resolveRoleName($role);
        }, $rolesArray);
    }

    /**
     * Resolve the role name from a Role instance or a string.
     */
    private function resolveRoleName(Role|array|string $role): string
    {
        if (is_array($role)) {
            if (isset($role['name'])) {
                return $role['name'];
            }

            throw new RoleNotFoundException(json_encode($role));
        }

        return $role instanceof Role ? $role->name : $role;
    }
}
