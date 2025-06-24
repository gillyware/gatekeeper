<?php

namespace Braxey\Gatekeeper\Exceptions;

class RolesFeatureDisabledException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'roles' feature is disabled. Please enable it in the configuration.");
    }
}
