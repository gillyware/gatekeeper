<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Contracts\Support\Arrayable;

trait HasRoles
{
    use InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a role to the model.
     */
    public function assignRole(string $roleName): bool
    {
        return Gatekeeper::assignRoleToModel($this, $roleName);
    }

    /**
     * Assign multiple roles to the model.
     */
    public function assignRoles(array|Arrayable $roleNames): bool
    {
        return Gatekeeper::assignRolesToModel($this, $roleNames);
    }

    /**
     * Revoke a role from the model.
     */
    public function revokeRole(string $roleName): bool
    {
        return Gatekeeper::revokeRoleFromModel($this, $roleName);
    }

    /**
     * Revoke multiple roles from the model.
     */
    public function revokeRoles(array|Arrayable $roleNames): bool
    {
        return Gatekeeper::revokeRolesFromModel($this, $roleNames);
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(string $roleName): bool
    {
        return Gatekeeper::modelHasRole($this, $roleName);
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole(array|Arrayable $roleNames): bool
    {
        return Gatekeeper::modelHasAnyRole($this, $roleNames);
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles(array|Arrayable $roleNames): bool
    {
        return Gatekeeper::modelHasAllRoles($this, $roleNames);
    }
}
