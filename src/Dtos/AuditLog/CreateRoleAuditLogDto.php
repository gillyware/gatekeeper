<?php

namespace Braxey\Gatekeeper\Dtos\AuditLog;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Models\Role;

class CreateRoleAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Role $role)
    {
        parent::__construct();

        $this->metadata = [
            'name' => $role->name,
        ];
    }

    /**
     * Get the action for the audit log.
     */
    public function getAction(): string
    {
        return Action::ROLE_CREATE;
    }
}
