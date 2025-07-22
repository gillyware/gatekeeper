<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;

abstract class AbstractBaseEntityAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(AbstractBaseEntityModel $entityModel): static
    {
        return parent::from([
            'action_to' => $entityModel,
            'metadata' => ['name' => $entityModel->name],
        ]);
    }
}
