<?php

namespace Gillyware\Gatekeeper\Exceptions;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Enums\AuditLogActionVerb;

class GatekeeperConsoleExceptionBuilder extends GatekeeperExceptionBuilder
{
    public function noConfiguredModels(): static
    {
        return $this->setMessage('No models are specified in the Gatekeeper configuration.');
    }

    public function noSearchableColumns(string $modelLabel): static
    {
        return $this->setMessage("No columns are searchable for [$modelLabel] models.");
    }

    public function noSearchResults(AuditLogAction $action): static
    {
        [$entity, $verb] = [$action->getEntity()->value, $action->getVerb()->value];

        return $this->setMessage(match ($verb) {
            AuditLogActionVerb::UpdateName->value,
            AuditLogActionVerb::Delete->value,
            AuditLogActionVerb::Assign->value,
            AuditLogActionVerb::Unassign->value,
            AuditLogActionVerb::Deny->value,
            AuditLogActionVerb::Undeny->value, => "No {$entity}s found.",
            AuditLogActionVerb::GrantByDefault->value => "No {$entity}s not granted by default found.",
            AuditLogActionVerb::RevokeDefaultGrant->value => "No {$entity}s granted by default found.",
            AuditLogActionVerb::Deactivate->value => "No active {$entity}s found.",
            AuditLogActionVerb::Reactivate->value => "No inactive {$entity}s found.",
            default => "No {$entity}s found.",
        });
    }
}
