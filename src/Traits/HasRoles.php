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
        return Gatekeeper::for($this)->assignRole($role);
    }

    /**
     * Assign multiple roles to the model.
     */
    public function assignAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->assignAllRoles($roles);
    }

    /**
     * Revoke a role from the model.
     */
    public function revokeRole(Role|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->revokeRole($role);
    }

    /**
     * Revoke multiple roles from the model.
     */
    public function revokeAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->revokeAllRoles($roles);
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(Role|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->hasRole($role);
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->hasAnyRole($roles);
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->hasAllRoles($roles);
    }
}
