<?php

namespace Braxey\Gatekeeper\Models;

use Braxey\Gatekeeper\Database\Factories\RoleFactory;
use Braxey\Gatekeeper\Traits\HasPermissions;
use Illuminate\Support\Facades\Config;

class Role extends AbstractGatekeeperEntity
{
    use HasPermissions;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.roles');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
