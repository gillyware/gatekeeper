<?php

namespace Braxey\Gatekeeper\Models;

use Braxey\Gatekeeper\Database\Factories\PermissionFactory;
use Illuminate\Support\Facades\Config;

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
}
