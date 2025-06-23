<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasRole;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Contracts\Support\Arrayable;

trait HasRoles
{
    use InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a role to the model.
     */
    public function assignRole(string $roleName): bool
    {
        // If roles are disabled, we cannot assign roles.
        if (! config('gatekeeper.features.roles', false)) {
            throw new \RuntimeException('Cannot assign roles when the roles feature is disabled.');
        }

        $role = $this->resolveRoleByName($roleName);

        $builder = ModelHasRole::forModel($this)->where('role_id', $role->id);

        // Check if the model already has this role directly assigned.
        $modelAlreadyDirectlyHasRole = $builder->whereNull('deleted_at')->exists();

        // If the model already has this role directly assigned, we don't need to sync again.
        if ($modelAlreadyDirectlyHasRole) {
            return true;
        }

        // Insert the role assignment.
        $modelHasRole = new ModelHasRole([
            'role_id' => $role->id,
            'model_type' => $this->getMorphClass(),
            'model_id' => $this->getKey(),
        ]);

        return $modelHasRole->save();
    }

    /**
     * Assign multiple roles to the model.
     */
    public function assignRoles(array|Arrayable $roleNames): bool
    {
        $result = true;

        foreach ($this->roleNamesArray($roleNames) as $roleName) {
            $result = $result && $this->assignRole($roleName);
        }

        return $result;
    }

    /**
     * Revoke a role from the model.
     */
    public function revokeRole(string $roleName): bool
    {
        // If roles are disabled, we cannot revoke roles.
        if (! config('gatekeeper.features.roles', false)) {
            throw new \RuntimeException('Cannot revoke roles when the roles feature is disabled.');
        }

        $role = $this->resolveRoleByName($roleName);

        ModelHasRole::forModel($this)
            ->where('role_id', $role->id)
            ->whereNull('deleted_at')
            ->delete();

        return true;
    }

    /**
     * Revoke multiple roles from the model.
     */
    public function revokeRoles(array|Arrayable $roleNames): bool
    {
        $result = true;

        foreach ($this->roleNamesArray($roleNames) as $roleName) {
            $result = $result && $this->revokeRole($roleName);
        }

        return $result;
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(string $roleName): bool
    {
        if (! config('gatekeeper.features.roles', false)) {
            return false;
        }

        $role = $this->resolveRoleByName($roleName);

        // If the role is not active, we can immediately return false.
        if (! $role->is_active) {
            return false;
        }

        // Check if the role is directly assigned to the model.
        $hasDirectRole = ModelHasRole::forModel($this)
            ->where('role_id', $role->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->exists();

        // If the role is currently directly assigned to the model, return true.
        if ($hasDirectRole) {
            return true;
        }

        // If teams are enabled, check if the model has the role through teams.
        if (config('gatekeeper.features.teams', false)) {
            $teamsTable = config('gatekeeper.tables.teams', 'teams');

            $hasTeamWithRole = $this->teams()
                ->withTrashed()
                ->whereNull("$teamsTable.deleted_at")
                ->whereNull('model_has_teams.deleted_at')
                ->where('is_active', true)
                ->get()
                ->filter(fn ($team) => $team->hasRole($roleName))
                ->isNotEmpty();

            if ($hasTeamWithRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole(array|Arrayable $roleNames): bool
    {
        foreach ($this->roleNamesArray($roleNames) as $roleName) {
            if ($this->hasRole($roleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles(array|Arrayable $roleNames): bool
    {
        foreach ($this->roleNamesArray($roleNames) as $roleName) {
            if (! $this->hasRole($roleName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a role by its name.
     */
    private function resolveRoleByName(string $roleName): Role
    {
        return Role::where('name', $roleName)->firstOrFail();
    }

    /**
     * Convert an array or Arrayable object of role names to an array.
     */
    private function roleNamesArray(array|Arrayable $roleNames): array
    {
        return $roleNames instanceof Arrayable ? $roleNames->toArray() : $roleNames;
    }
}
