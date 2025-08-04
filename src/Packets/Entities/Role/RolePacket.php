<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Role;

use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Traits\PacketHasPermissions;

final class RolePacket extends AbstractBaseEntityPacket
{
    use PacketHasPermissions;

    protected function getModel(): Role
    {
        return resolve(RoleRepository::class)->findOrFailByName($this->name);
    }
}
