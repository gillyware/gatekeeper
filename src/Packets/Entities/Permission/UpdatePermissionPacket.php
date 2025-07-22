<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Permission;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseUpdateEntityPacket;

final class UpdatePermissionPacket extends AbstractBaseUpdateEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Permission)->getTable();
    }

    protected static function getEntityId(): int
    {
        return (int) request()->route('permission')?->id;
    }
}
