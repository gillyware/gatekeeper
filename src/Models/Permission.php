<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Factories\PermissionFactory;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Illuminate\Support\Facades\Config;

/**
 * @extends AbstractBaseEntityModel<PermissionFactory, PermissionPacket>
 */
class Permission extends AbstractBaseEntityModel
{
    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }

    protected static function packetClass(): string
    {
        return PermissionPacket::class;
    }

    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
    }
}
