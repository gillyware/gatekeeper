<?php

namespace Braxey\Gatekeeper\Dtos\AuditLog;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RevokeRoleAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Model $actionToModel, Role $role)
    {
        parent::__construct();

        $this->actionToModel = $actionToModel;

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
        return Action::ROLE_REVOKE;
    }
}
