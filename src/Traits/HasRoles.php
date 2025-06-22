<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasRole;
use Braxey\Gatekeeper\Models\Role;

trait HasRoles
{
    use InteractsWithRoles;

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
     * Revoke a role from the model.
     */
    public function revokeRole(string $roleName): int
    {
        // If roles are disabled, we cannot revoke roles.
        if (! config('gatekeeper.features.roles', false)) {
            throw new \RuntimeException('Cannot revoke roles when the roles feature is disabled.');
        }

        $role = $this->resolveRoleByName($roleName);

        return ModelHasRole::forModel($this)
            ->where('role_id', $role->id)
            ->whereNull('deleted_at')
            ->delete();
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(string $roleName): bool
    {
        $rolesEnabled = config('gatekeeper.features.roles', false);
        // $teamsEnabled = config('gatekeeper.features.teams', false);

        // If roles are disabled, return false immediately.
        if (! $rolesEnabled) {
            return false;
        }

        $role = $this->resolveRoleByName($roleName);

        // If the role is not active, we can immediately return false.
        if (! $role->is_active) {
            return false;
        }

        // Check if the role is directly assigned to the model.
        $roleDirectlyAssigned = ModelHasRole::forModel($this)
            ->where('role_id', $role->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->first();

        // If the role is currently directly assigned to the model, return true.
        if ($roleDirectlyAssigned) {
            return true;
        }

        // TODO: Implement team-based role checks if teams are enabled.

        // Return false by default.
        return false;
    }

    /**
     * Get a role by its name.
     *
     * @return \Braxey\Gatekeeper\Models\Role
     */
    private function resolveRoleByName(string $roleName)
    {
        return Role::where('name', $roleName)->firstOrFail();
    }
}
