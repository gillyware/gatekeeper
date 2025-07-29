<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Feature;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAuditLogPacket;

final class RevokedFeatureDefaultGrantAuditLogPacket extends AbstractBaseEntityAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::RevokeFeatureDefaultGrant;
    }
}
