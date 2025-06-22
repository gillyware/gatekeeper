<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Role;

trait InteractsWithRoles
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
}
