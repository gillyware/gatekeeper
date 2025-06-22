<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Permission;

trait InteractsWithPermissions
{
    /**
     * Get the permissions associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions()
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions', 'model_id', 'permission_id')->withTrashed();
    }
}
