<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Factories\RoleFactory;
use Gillyware\Gatekeeper\Packets\RolePacket;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Illuminate\Support\Facades\Config;

/**
 * @extends AbstractBaseEntityModel<RoleFactory, RolePacket>
 */
class Role extends AbstractBaseEntityModel
{
    use HasPermissions;

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    protected static function packetClass(): string
    {
        return RolePacket::class;
    }

    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }
}
