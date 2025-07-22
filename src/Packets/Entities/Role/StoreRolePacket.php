<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Role;

use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseStoreEntityPacket;

final class StoreRolePacket extends AbstractBaseStoreEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Role)->getTable();
    }
}
