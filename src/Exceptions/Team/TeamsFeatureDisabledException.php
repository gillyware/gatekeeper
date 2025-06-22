<?php

namespace Gillyware\Gatekeeper\Exceptions\Team;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class TeamsFeatureDisabledException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'teams' feature is disabled. Please enable it in the configuration.");
    }
}
