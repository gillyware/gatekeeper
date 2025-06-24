<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Contracts\Support\Arrayable;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(string $permissionName): bool
    {
        return Gatekeeper::assignPermissionToModel($this, $permissionName);
    }

    /**
     * Assign multiple permissions to the model.
     */
    public function assignPermissions(array|Arrayable $permissionNames): bool
    {
        return Gatekeeper::assignPermissionsToModel($this, $permissionNames);
    }

    /**
     * Revoke a permission from the model.
     */
    public function revokePermission(string $permissionName): bool
    {
        return Gatekeeper::revokePermissionFromModel($this, $permissionName);
    }

    /**
     * Revoke multiple permissions from the model.
     */
    public function revokePermissions(array|Arrayable $permissionNames): bool
    {
        return Gatekeeper::revokePermissionsFromModel($this, $permissionNames);
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return Gatekeeper::modelHasPermission($this, $permissionName);
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission(array|Arrayable $permissionNames): bool
    {
        return Gatekeeper::modelHasAnyPermission($this, $permissionNames);
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions(array|Arrayable $permissionNames): bool
    {
        return Gatekeeper::modelHasAllPermissions($this, $permissionNames);
    }
}
