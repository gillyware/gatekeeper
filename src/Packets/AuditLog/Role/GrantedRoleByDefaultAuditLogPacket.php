<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Role;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAuditLogPacket;

final class GrantedRoleByDefaultAuditLogPacket extends AbstractBaseEntityAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::GrantRoleByDefault;
    }
}
