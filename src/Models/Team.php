<?php

namespace Braxey\Gatekeeper\Models;

use Braxey\Gatekeeper\Database\Factories\TeamFactory;
use Braxey\Gatekeeper\Traits\HasPermissions;
use Braxey\Gatekeeper\Traits\HasRoles;
use Illuminate\Support\Facades\Config;

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
}
