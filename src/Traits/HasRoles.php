<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Role;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
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

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<Role>
     */
    public function getDirectRoles(): Collection
    {
        return Gatekeeper::for($this)->getDirectRoles();
    }

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<Role>
     */
    public function getEffectiveRoles(): Collection
    {
        return Gatekeeper::for($this)->getEffectiveRoles();
    }

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseRoles(): Collection
    {
        return Gatekeeper::for($this)->getVerboseRoles();
    }
}
