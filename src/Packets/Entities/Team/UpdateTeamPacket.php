<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Team;

use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseUpdateEntityPacket;

final class UpdateTeamPacket extends AbstractBaseUpdateEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Team)->getTable();
    }

    protected static function getEntityId(): int
    {
        return (int) request()->route('team')?->id;
    }
}
