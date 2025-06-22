<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;

trait HasPermissions
{
    use InteractsWithPermissions, InteractsWithRoles, InteractsWithTeams;

    /**
     * Assign a permission to the model.
     */
    public function assignPermission(string $permissionName): bool
    {
        $permission = $this->resolvePermissionByName($permissionName);

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
        $permission = $this->resolvePermissionByName($permissionName);

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

        // If teams are enabled, check if the model has the permission through the teams roles or permissions.
        if (config('gatekeeper.features.teams', false)) {
            $teamsTableName = config('gatekeeper.tables.teams', 'teams');

            $activeModelTeamsWithPermission = $this->teams()
                ->whereNull("$teamsTableName.deleted_at")
                ->whereNull('model_has_teams.deleted_at')
                ->where('is_active', true)
                ->get()
                ->filter(fn ($team) => $team->hasPermission($permission->name));

            // If the model has any active teams with the permission, return true.
            if ($activeModelTeamsWithPermission->isNotEmpty()) {
                return true;
            }
        }

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
