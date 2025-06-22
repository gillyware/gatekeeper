<?php

namespace Gillyware\Gatekeeper\Exceptions\Team;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class DeletingAssignedTeamException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $teamName)
    {
        parent::__construct("Cannot delete team '{$teamName}' because it is assigned to one or more models.");
    }
}
