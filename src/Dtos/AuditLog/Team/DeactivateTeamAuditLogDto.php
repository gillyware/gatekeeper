<?php

namespace Gillyware\Gatekeeper\Dtos\AuditLog\Team;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Team;

class DeactivateTeamAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Team $team)
    {
        parent::__construct();

        $this->actionToModel = $team;

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
        return Action::TEAM_DEACTIVATE;
    }
}
