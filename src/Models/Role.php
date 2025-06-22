<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Database\Factories\RoleFactory;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Check if the table for this model exists in the database.
     */
    public static function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.roles'));
    }
}
