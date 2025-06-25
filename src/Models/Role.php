<?php

namespace Braxey\Gatekeeper\Models;

use Braxey\Gatekeeper\Database\Factories\RoleFactory;
use Braxey\Gatekeeper\Traits\HasPermissions;

class Role extends AbstractGatekeeperEntity
{
    use HasPermissions;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('gatekeeper.tables.roles', 'roles');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
