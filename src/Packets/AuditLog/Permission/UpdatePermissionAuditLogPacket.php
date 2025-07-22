<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Permission;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseStoreAuditLogPacket;

final class UpdatePermissionAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(Permission $permission, string $oldPermissionName): static
    {
        return parent::from([
            'action_to' => $permission,
            'metadata' => [
                'name' => $permission->name,
                'old_name' => $oldPermissionName,
            ],
        ]);
    }

    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UpdatePermission;
    }
}
