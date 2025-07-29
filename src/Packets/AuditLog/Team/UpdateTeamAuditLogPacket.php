<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Team;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseStoreAuditLogPacket;

final class UpdateTeamAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(Team $team, string $oldTeamName): static
    {
        return parent::from([
            'action_to' => $team,
            'metadata' => [
                'name' => $team->name,
                'old_name' => $oldTeamName,
            ],
        ]);
    }

    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UpdateTeamName;
    }
}
