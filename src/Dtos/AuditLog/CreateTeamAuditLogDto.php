<?php

namespace Braxey\Gatekeeper\Dtos\AuditLog;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Models\Team;

class CreateTeamAuditLogDto extends AbstractAuditLogDto
{
    public function __construct(Team $team)
    {
        parent::__construct();

        $this->metadata = [
            'name' => $team->name,
        ];
    }

    /**
     * Get the action for the audit log.
     */
    public function getAction(): string
    {
        return Action::TEAM_CREATE;
    }
}
