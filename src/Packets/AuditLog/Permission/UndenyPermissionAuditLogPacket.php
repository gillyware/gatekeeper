<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Permission;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAssignmentAuditLogPacket;

final class UndenyPermissionAuditLogPacket extends AbstractBaseEntityAssignmentAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UndenyPermission;
    }
}
