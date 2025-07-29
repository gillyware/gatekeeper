<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Permission;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAuditLogPacket;

final class GrantedPermissionByDefaultAuditLogPacket extends AbstractBaseEntityAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::GrantPermissionByDefault;
    }
}
