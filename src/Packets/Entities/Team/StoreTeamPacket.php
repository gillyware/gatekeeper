<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Team;

use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseStoreEntityPacket;

final class StoreTeamPacket extends AbstractBaseStoreEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Team)->getTable();
    }
}
