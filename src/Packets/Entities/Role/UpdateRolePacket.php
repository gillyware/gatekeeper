<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Role;

use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseUpdateEntityPacket;

final class UpdateRolePacket extends AbstractBaseUpdateEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Role)->getTable();
    }

    protected static function getEntityId(): int
    {
        return (int) request()->route('role')?->id;
    }
}
