<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;

trait InteractsWithPermissions
{
    /**
     * Get the permissions associated with the model.
     */
    public function permissions(): MorphToMany
    {
        $modelHasPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions');

        return $this->morphToMany(Permission::class, 'model', $modelHasPermissionsTable, 'model_id', 'permission_id');
    }
}
