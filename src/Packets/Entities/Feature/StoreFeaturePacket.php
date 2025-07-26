<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Feature;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseStoreEntityPacket;

final class StoreFeaturePacket extends AbstractBaseStoreEntityPacket
{
    protected static function getTableName(): string
    {
        return (new Feature)->getTable();
    }
}
