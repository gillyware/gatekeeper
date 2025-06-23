<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(string $permissionName): bool
    {
        $permission = $this->permissionRepository()->findByName($permissionName);

        $builder = ModelHasPermission::forModel($this)->where('permission_id', $permission->id);

        // Check if the model already has this permission directly assigned.
        $hasDirectPermission = $builder->whereNull('deleted_at')->exists();

        // If the model already has this permission directly assigned, we don't need to sync again.
        if ($hasDirectPermission) {
            return true;
        }

        // Insert the permission assignment.
        $modelHasPermission = new ModelHasPermission([
            'permission_id' => $permission->id,
            'model_type' => $this->getMorphClass(),
            'model_id' => $this->getKey(),
        ]);

        return $modelHasPermission->save();
    }

    /**
     * Assign multiple permissions to the model.
     */
    public function assignPermissions(array|Arrayable $permissionNames): bool
    {
        $result = true;

        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            $result = $result && $this->assignPermission($permissionName);
        }

        return $result;
    }

    /**
     * Revoke a permission from the model.
     */
    public function revokePermission(string $permissionName): bool
    {
        $permission = $this->permissionRepository()->findByName($permissionName);

        $revokedAll = true;

        ModelHasPermission::forModel($this)
            ->where('permission_id', $permission->id)
            ->whereNull('deleted_at')
            ->get()
            ->each(function (ModelHasPermission $modelHasPermission) use (&$revokedAll) {
                $revokedAll = $modelHasPermission->delete() && $revokedAll;
            });

        return $revokedAll;
    }

    /**
     * Revoke multiple permissions from the model.
     */
    public function revokePermissions(array|Arrayable $permissionNames): bool
    {
        $result = true;

        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            $result = $result && $this->revokePermission($permissionName);
        }

        return $result;
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        $permission = $this->permissionRepository()->findByName($permissionName);

        // If the permission is not active, we can immediately return false.
        if (! $permission->is_active) {
            return false;
        }

        // Fetch the most recent permission assignment.
        $recentPermissionAssignment = ModelHasPermission::forModel($this)
            ->where('permission_id', $permission->id)
            ->orderByDesc('created_at')
            ->first();

        // If we find a direct permission assignment, we can use it to determine if the model has the permission.
        if ($recentPermissionAssignment) {
            return ! $recentPermissionAssignment->deleted_at;
        }

        // If roles are enabled, check if the model has the permission through roles.
        if (config('gatekeeper.features.roles', false)) {
            $hasRoleWithPermission = $this->roleRepository()
                ->getActiveForModel($this)
                ->some(fn (Role $role) => $role->hasPermission($permission->name));

            // If the model has any active roles with the permission, return true.
            if ($hasRoleWithPermission) {
                return true;
            }
        }

        // If teams are enabled, check if the model has the permission through the teams roles or permissions.
        if (config('gatekeeper.features.teams', false)) {
            $onTeamWithPermission = $this->teamRepository()
                ->getActiveForModel($this)
                ->some(fn (Team $team) => $team->hasPermission($permission->name));

            // If the model has any active teams with the permission, return true.
            if ($onTeamWithPermission) {
                return true;
            }
        }

        // Return false by default.
        return false;
    }

    /**
     * Check if the model has any of the given permissions.
     */
    public function hasAnyPermission(array|Arrayable $permissionNames): bool
    {
        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            if ($this->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given permissions.
     */
    public function hasAllPermissions(array|Arrayable $permissionNames): bool
    {
        foreach ($this->permissionNamesArray($permissionNames) as $permissionName) {
            if (! $this->hasPermission($permissionName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert an array or Arrayable object of permission names to an array.
     */
    private function permissionNamesArray(array|Arrayable $permissionNames): array
    {
        return $permissionNames instanceof Arrayable ? $permissionNames->toArray() : $permissionNames;
    }
}
