<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithPermissions
{
    /**
     * Get the permissions associated with the model.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions', 'model_id', 'permission_id');
    }
}
