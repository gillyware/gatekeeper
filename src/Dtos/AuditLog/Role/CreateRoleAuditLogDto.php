<?php

namespace Gillyware\Gatekeeper\Dtos\AuditLog\Role;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Role;

class CreateRoleAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Role $role)
    {
        parent::__construct();

        $this->actionToModel = $role;

        $this->metadata = [
            'name' => $role->name,
            'lifecycle_id' => Gatekeeper::getLifecycleId(),
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
