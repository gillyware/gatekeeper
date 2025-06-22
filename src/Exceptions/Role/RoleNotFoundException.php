<?php

namespace Gillyware\Gatekeeper\Exceptions\Role;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class RoleNotFoundException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $roleName)
    {
        parent::__construct("Role '{$roleName}' not found.");
    }
}
