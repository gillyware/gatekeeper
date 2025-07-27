<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Feature;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseEntityAuditLogPacket;

final class TurnedOnByDefaultFeatureAuditLogPacket extends AbstractBaseEntityAuditLogPacket
{
    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::TurnFeatureOnByDefault;
    }
}
