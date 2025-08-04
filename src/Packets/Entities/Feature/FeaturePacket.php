<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Feature;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Traits\PacketHasPermissions;

final class FeaturePacket extends AbstractBaseEntityPacket
{
    use PacketHasPermissions;

    protected function getModel(): Feature
    {
        return resolve(FeatureRepository::class)->findOrFailByName($this->name);
    }
}
