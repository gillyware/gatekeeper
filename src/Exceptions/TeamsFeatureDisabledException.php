<?php

namespace Braxey\Gatekeeper\Exceptions;

class TeamsFeatureDisabledException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'teams' feature is disabled. Please enable it in the configuration.");
    }
}
