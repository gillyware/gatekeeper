<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithRoles
{
    /**
     * Get the roles associated with the model.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles', 'model_id', 'role_id');
    }
}
