<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasRoles
{
    use InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a role to the model.
     */
    public function assignRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->assignRole($role);
    }

    /**
     * Assign multiple roles to the model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function assignAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->assignAllRoles($roles);
    }

    /**
     * Unassign a role from the model.
     */
    public function unassignRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->unassignRole($role);
    }

    /**
     * Unassign multiple roles from the model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function unassignAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->unassignAllRoles($roles);
    }

    /**
     * Deny a role from the model.
     */
    public function denyRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->denyRole($role);
    }

    /**
     * Deny multiple roles from the model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function denyAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->denyAllRoles($roles);
    }

    /**
     * Undeny a role from the model.
     */
    public function undenyRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->undenyRole($role);
    }

    /**
     * Undeny multiple roles from the model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function undenyAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->undenyAllRoles($roles);
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::for($this)->hasRole($role);
    }

    /**
     * Check if the model has any of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function hasAnyRole(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->hasAnyRole($roles);
    }

    /**
     * Check if the model has all of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function hasAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::for($this)->hasAllRoles($roles);
    }

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<string, RolePacket>
     */
    public function getDirectRoles(): Collection
    {
        return Gatekeeper::for($this)->getDirectRoles();
    }

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<string, RolePacket>
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
