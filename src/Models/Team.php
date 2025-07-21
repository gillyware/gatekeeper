<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Factories\TeamFactory;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Gillyware\Gatekeeper\Traits\HasRoles;
use Illuminate\Support\Facades\Config;

/**
 * @extends AbstractBaseEntityModel<TeamFactory, TeamPacket>
 */
class Team extends AbstractBaseEntityModel
{
    use HasPermissions;
    use HasRoles;

    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    protected static function packetClass(): string
    {
        return TeamPacket::class;
    }

    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS);
    }
}
