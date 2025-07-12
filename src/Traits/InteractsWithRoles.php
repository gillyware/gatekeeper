<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;

trait InteractsWithRoles
{
    /**
     * Get the roles associated with the model.
     */
    public function roles(): MorphToMany
    {
        $modelHasRolesTable = Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES);

        return $this->morphToMany(Role::class, 'model', $modelHasRolesTable, 'model_id', 'role_id');
    }
}
