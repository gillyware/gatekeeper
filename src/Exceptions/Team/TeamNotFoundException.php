<?php

namespace Gillyware\Gatekeeper\Exceptions\Team;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class TeamNotFoundException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $teamName)
    {
        parent::__construct("Team '{$teamName}' not found.");
    }
}
