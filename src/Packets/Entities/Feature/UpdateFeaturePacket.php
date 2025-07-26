<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Feature;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseUpdateEntityPacket;

final class UpdateFeaturePacket extends AbstractBaseUpdateEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Feature)->getTable();
    }

    protected static function getEntityId(): int
    {
        return (int) request()->route('feature')?->id;
    }
}
