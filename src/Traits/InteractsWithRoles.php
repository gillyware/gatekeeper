<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithRoles
{
    /**
     * Get the roles associated with the model.
     */
    public function roles(): MorphToMany
    {
        $modelHasRolesTable = (new ModelHasRole)->getTable();

        return $this->morphToMany(Role::class, 'model', $modelHasRolesTable, 'model_id', 'role_id');
    }
}
