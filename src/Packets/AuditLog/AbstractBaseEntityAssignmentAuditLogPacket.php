<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractBaseEntityAssignmentAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(Model $actionTo, AbstractBaseEntityModel $entityModel): static
    {
        return parent::from([
            'action_to' => $actionTo,
            'metadata' => ['name' => $entityModel->name],
        ]);
    }
}
