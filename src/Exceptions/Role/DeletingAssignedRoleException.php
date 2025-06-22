<?php

namespace Gillyware\Gatekeeper\Exceptions\Role;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class DeletingAssignedRoleException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $roleName)
    {
        parent::__construct("Cannot delete role '{$roleName}' because it is assigned to one or more models.");
    }
}
