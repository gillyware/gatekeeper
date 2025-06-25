<?php

namespace Braxey\Gatekeeper\Dtos\AuditLog;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Model;

class AssignTeamAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Model $actionToModel, Team $team)
    {
        parent::__construct();

        $this->actionToModel = $actionToModel;

        $this->metadata = [
            'name' => $team->name,
            'lifecycle_id' => Gatekeeper::getLifecycleId(),
        ];
    }

    /**
     * Get the action for the audit log.
     */
    public function getAction(): string
    {
        return Action::TEAM_ADD;
    }
}
