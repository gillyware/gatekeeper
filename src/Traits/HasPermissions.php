<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->assignPermission($permission);
    }

    /**
     * Assign multiple permissions to the model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function assignAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->assignAllPermissions($permissions);
    }

    /**
     * Unassign a permission from the model.
     */
    public function unassignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->unassignPermission($permission);
    }

    /**
     * Unassign multiple permissions from the model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function unassignAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->unassignAllPermissions($permissions);
    }

    /**
     * Deny a permission from the model.
     */
    public function denyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->denyPermission($permission);
    }

    /**
     * Deny multiple permissions from the model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function denyAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->denyAllPermissions($permissions);
    }

    /**
     * Undeny a permission from the model.
     */
    public function undenyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->undenyPermission($permission);
    }

    /**
     * Undeny multiple permissions from the model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function undenyAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->undenyAllPermissions($permissions);
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::for($this)->hasPermission($permission);
    }

    /**
     * Check if the model has any of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->hasAnyPermission($permissions);
    }

    /**
     * Check if the model has all of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::for($this)->hasAllPermissions($permissions);
    }

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<string, PermissionPacket>
     */
    public function getDirectPermissions(): Collection
    {
        return Gatekeeper::for($this)->getDirectPermissions();
    }

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<string, PermissionPacket>
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
