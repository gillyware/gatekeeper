<?php

namespace Braxey\Gatekeeper\Exceptions;

class RoleNotFoundException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $roleName)
    {
        parent::__construct("Role '{$roleName}' not found.");
    }
}
