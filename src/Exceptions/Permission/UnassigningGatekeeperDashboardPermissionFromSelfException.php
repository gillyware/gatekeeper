<?php

namespace Gillyware\Gatekeeper\Exceptions\Permission;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class UnassigningGatekeeperDashboardPermissionFromSelfException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('You may not unassign a Gatekeeper dashboard permission from yourself.');
    }
}
