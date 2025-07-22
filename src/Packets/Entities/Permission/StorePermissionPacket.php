<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Permission;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseStoreEntityPacket;

final class StorePermissionPacket extends AbstractBaseStoreEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Permission)->getTable();
    }
}
