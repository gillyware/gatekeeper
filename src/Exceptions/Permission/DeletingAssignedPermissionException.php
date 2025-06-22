<?php

namespace Gillyware\Gatekeeper\Exceptions\Permission;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class DeletingAssignedPermissionException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $permissionName)
    {
        parent::__construct("Cannot delete permission '{$permissionName}' because it is assigned to one or more models.");
    }
}
