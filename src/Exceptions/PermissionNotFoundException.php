<?php

namespace Braxey\Gatekeeper\Exceptions;

class PermissionNotFoundException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $permissionName)
    {
        parent::__construct("Permission '{$permissionName}' not found.");
    }
}
