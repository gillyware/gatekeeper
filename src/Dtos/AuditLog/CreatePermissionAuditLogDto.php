<?php

namespace Braxey\Gatekeeper\Dtos\AuditLog;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Models\Permission;

class CreatePermissionAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Permission $permission)
    {
        parent::__construct();

        $this->metadata = [
            'name' => $permission->name,
        ];
    }

    /**
     * Get the action for the audit log.
     */
    public function getAction(): string
    {
        return Action::PERMISSION_CREATE;
    }
}
