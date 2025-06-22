<?php

namespace Gillyware\Gatekeeper\Exceptions\Role;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class RolesFeatureDisabledException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'roles' feature is disabled. Please enable it in the configuration.");
    }
}
