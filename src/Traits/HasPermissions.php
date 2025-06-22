<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Contracts\Support\Arrayable;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(Permission|string $permission): bool
    {
        return Gatekeeper::assignPermissionToModel($this, $permission);
    }

    /**
     * Assign multiple permissions to the model.
     */
    public function assignPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::assignPermissionsToModel($this, $permissions);
    }

    /**
     * Revoke a permission from the model.
     */
    public function revokePermission(Permission|string $permission): bool
    {
        return Gatekeeper::revokePermissionFromModel($this, $permission);
    }

    /**
     * Revoke multiple permissions from the model.
     */
    public function revokePermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::revokePermissionsFromModel($this, $permissions);
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermission(Permission|string $permission): bool
    {
        return Gatekeeper::modelHasPermission($this, $permission);
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool
    {
        return Gatekeeper::modelHasAnyPermission($this, $permissions);
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::modelHasAllPermissions($this, $permissions);
    }
}
