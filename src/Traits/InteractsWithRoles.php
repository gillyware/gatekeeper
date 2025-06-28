<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

trait InteractsWithRoles
{
    /**
     * Get the active roles associated with the model.
     */
    public function getActiveRoleNames(): Collection
    {
        return $this->roleRepository()->getActiveNamesForModel($this);
    }

    /**
     * Get the roles associated with the model.
     */
    public function roles(): MorphToMany
    {
        $modelHasRolesTable = Config::get('gatekeeper.tables.model_has_roles');

        return $this->morphToMany(Role::class, 'model', $modelHasRolesTable, 'model_id', 'role_id');
    }

    /**
     * Get the role repository instance.
     */
    private function roleRepository(): RoleRepository
    {
        return app(RoleRepository::class);
    }
}
