<?php

namespace Gillyware\Gatekeeper\Exceptions\Permission;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class PermissionNotFoundException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $permissionName)
    {
        parent::__construct("Permission '{$permissionName}' not found.");
    }
}
