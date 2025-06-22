<?php

namespace Gillyware\Gatekeeper\Dtos\AuditLog\Permission;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;

class DeactivatePermissionAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Permission $permission)
    {
        parent::__construct();

        $this->actionToModel = $permission;

        $this->metadata = [
            'name' => $permission->name,
            'lifecycle_id' => Gatekeeper::getLifecycleId(),
        ];
    }

    /**
     * Get the action for the audit log.
     */
    public function getAction(): string
    {
        return Action::PERMISSION_DEACTIVATE;
    }
}
