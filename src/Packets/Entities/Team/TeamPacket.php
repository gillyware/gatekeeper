<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Team;

use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Traits\PacketHasFeatures;
use Gillyware\Gatekeeper\Traits\PacketHasPermissions;
use Gillyware\Gatekeeper\Traits\PacketHasRoles;

final class TeamPacket extends AbstractBaseEntityPacket
{
    use PacketHasFeatures;
    use PacketHasPermissions;
    use PacketHasRoles;

    protected function getModel(): Team
    {
        return resolve(TeamRepository::class)->findOrFailByName($this->name);
    }
}
