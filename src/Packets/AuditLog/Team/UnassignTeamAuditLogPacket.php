<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Team;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAssignmentAuditLogPacket;

final class UnassignTeamAuditLogPacket extends AbstractBaseEntityAssignmentAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UnassignTeam;
    }
}
