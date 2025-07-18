<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(Permission|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->assignPermission($permission);
    }

    /**
     * Assign multiple permissions to the model.
     */
    public function assignAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->assignAllPermissions($permissions);
    }

    /**
     * Revoke a permission from the model.
     */
    public function revokePermission(Permission|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->revokePermission($permission);
    }

    /**
     * Revoke multiple permissions from the model.
     */
    public function revokeAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->revokeAllPermissions($permissions);
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermission(Permission|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->hasPermission($permission);
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->hasAnyPermission($permissions);
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->hasAllPermissions($permissions);
    }

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<Permission>
     */
    public function getDirectPermissions(): Collection
    {
        return Gatekeeper::for($this)->getDirectPermissions();
    }

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<Permission>
     */
    public function getEffectivePermissions(): Collection
    {
        return Gatekeeper::for($this)->getEffectivePermissions();
    }

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerbosePermissions(): Collection
    {
        return Gatekeeper::for($this)->getVerbosePermissions();
    }
}
