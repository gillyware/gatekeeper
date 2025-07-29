<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Role;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseStoreAuditLogPacket;

final class UpdateRoleAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(Role $role, string $oldRoleName): static
    {
        return parent::from([
            'action_to' => $role,
            'metadata' => [
                'name' => $role->name,
                'old_name' => $oldRoleName,
            ],
        ]);
    }

    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UpdateRoleName;
    }
}
