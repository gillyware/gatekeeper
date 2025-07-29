<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Role;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAssignmentAuditLogPacket;

final class UnassignRoleAuditLogPacket extends AbstractBaseEntityAssignmentAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UnassignRole;
    }
}
