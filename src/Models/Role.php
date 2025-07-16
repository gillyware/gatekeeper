<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Factories\RoleFactory;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Illuminate\Support\Facades\Config;

class Role extends AbstractBaseEntityModel
{
    use HasPermissions;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
