<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithPermissions
{
    /**
     * Get the permissions associated with the model.
     */
    public function permissions(): MorphToMany
    {
        $modelHasPermissionsTable = (new ModelHasPermission)->getTable();

        return $this->morphToMany(Permission::class, 'model', $modelHasPermissionsTable, 'model_id', 'permission_id');
    }
}
