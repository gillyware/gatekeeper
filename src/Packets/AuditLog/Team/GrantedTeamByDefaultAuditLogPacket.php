<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Team;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAuditLogPacket;

final class GrantedTeamByDefaultAuditLogPacket extends AbstractBaseEntityAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::GrantTeamByDefault;
    }
}
