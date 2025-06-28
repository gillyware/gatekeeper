<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

trait InteractsWithPermissions
{
    /**
     * Get the active permissions associated with the model.
     */
    public function getActivePermissionNames(): Collection
    {
        return $this->permissionRepository()->getActiveNamesForModel($this);
    }

    /**
     * Get the permissions associated with the model.
     */
    public function permissions(): MorphToMany
    {
        $modelHasPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions');

        return $this->morphToMany(Permission::class, 'model', $modelHasPermissionsTable, 'model_id', 'permission_id');
    }

    /**
     * Get the permission repository instance.
     */
    private function permissionRepository(): PermissionRepository
    {
        return app(PermissionRepository::class);
    }
}
