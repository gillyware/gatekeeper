<?php

namespace Gillyware\Gatekeeper\Exceptions\Permission;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class RevokingGatekeeperDashboardPermissionFromSelfException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('You may not revoke a Gatekeeper dashboard permission from yourself.');
    }
}
