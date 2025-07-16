<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Role;
use Illuminate\Contracts\Support\Arrayable;
use UnitEnum;

trait HasRoles
{
    use InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a role to the model.
     */
    public function assignRole(Role|string|UnitEnum $role): bool
    {
        return Gatekeeper::assignRoleToModel($this, $role);
    }

    /**
     * Assign multiple roles to the model.
     */
    public function assignRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::assignRolesToModel($this, $roles);
    }

    /**
     * Revoke a role from the model.
     */
    public function revokeRole(Role|string|UnitEnum $role): bool
    {
        return Gatekeeper::revokeRoleFromModel($this, $role);
    }

    /**
     * Revoke multiple roles from the model.
     */
    public function revokeRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::revokeRolesFromModel($this, $roles);
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(Role|string|UnitEnum $role): bool
    {
        return Gatekeeper::modelHasRole($this, $role);
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole(array|Arrayable $roles): bool
    {
        return Gatekeeper::modelHasAnyRole($this, $roles);
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::modelHasAllRoles($this, $roles);
    }
}
