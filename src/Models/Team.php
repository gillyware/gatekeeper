<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Database\Factories\TeamFactory;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Gillyware\Gatekeeper\Traits\HasRoles;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class Team extends AbstractGatekeeperEntity
{
    use HasPermissions;
    use HasRoles;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.teams');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    /**
     * Check if the table for this model exists in the database.
     */
    public static function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.teams'));
    }
}
