<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Database\Factories\PermissionFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class Permission extends AbstractGatekeeperEntity
{
    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.permissions');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }

    /**
     * Check if the table for this model exists in the database.
     */
    public static function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.permissions'));
    }
}
