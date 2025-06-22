<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;

trait HasPermissions
{
    /**
     * Get the roles associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles', 'model_id', 'role_id')->withTrashed();
    }

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(string $permissionName): bool
    {
        $permission = $this->resolvePermissionByName($permissionName);

        $builder = ModelHasPermission::forModel($this)->where('permission_id', $permission->id);

        // Check if the model already has this permission directly assigned.
        $modelAlreadyDirectlyHasPermission = $builder->whereNull('deleted_at')->exists();

        // If the model already has this permission directly assigned, we don't need to sync again.
        if ($modelAlreadyDirectlyHasPermission) {
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
     * Revoke a permission from the model.
     */
    public function revokePermission(string $permissionName): bool
    {
        $permission = $this->resolvePermissionByName($permissionName);

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
     * Check if the model has a given permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        $rolesEnabled = config('gatekeeper.features.roles', false);
        // $teamsEnabled = config('gatekeeper.features.teams', false);
        $permission = $this->resolvePermissionByName($permissionName);

        // If the permission is not active, we can immediately return false.
        if (! $permission->is_active) {
            return false;
        }

        // Fetch the most recent permission assignment.
        $mostRecentPermission = ModelHasPermission::forModel($this)
            ->where('permission_id', $permission->id)
            ->orderByDesc('created_at')
            ->first();

        // If we find a direct permission assignment, we can use it to determine if the model has the permission.
        if ($mostRecentPermission) {
            return ! $mostRecentPermission->deleted_at;
        }

        // If roles are enabled, check if the model has the permission through roles.
        if ($rolesEnabled) {
            $rolesTableName = config('gatekeeper.tables.roles', 'roles');

            $activeModelRolesWithPermission = $this->roles()
                ->whereNull("$rolesTableName.deleted_at")
                ->whereNull('model_has_roles.deleted_at')
                ->where('is_active', true)
                ->get()
                ->filter(fn (Role $role) => $role->hasPermission($permission->name));

            // If the model has any active roles with the permission, return true.
            if ($activeModelRolesWithPermission->isNotEmpty()) {
                return true;
            }
        }

        // TODO: Implement team-based permission checks if teams are enabled.

        // Return false by default.
        return false;
    }

    /**
     * Get a permission by its name.
     *
     * @return \Braxey\Gatekeeper\Models\Permission
     */
    private function resolvePermissionByName(string $permissionName)
    {
        return Permission::where('name', $permissionName)->firstOrFail();
    }
}
