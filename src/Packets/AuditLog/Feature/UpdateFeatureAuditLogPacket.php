<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog\Feature;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseStoreAuditLogPacket;

final class UpdateFeatureAuditLogPacket extends AbstractBaseStoreAuditLogPacket
{
    public static function make(Feature $feature, string $oldFeatureName): static
    {
        return parent::from([
            'action_to' => $feature,
            'metadata' => [
                'name' => $feature->name,
                'old_name' => $oldFeatureName,
            ],
        ]);
    }

    protected static function getAction(): AuditLogAction
    {
        return AuditLogAction::UpdateFeature;
    }
}
