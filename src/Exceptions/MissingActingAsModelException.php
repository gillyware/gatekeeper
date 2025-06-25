<?php

namespace Braxey\Gatekeeper\Exceptions;

class MissingActingAsModelException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'audit' feature is enabled, but no acting as model is set.");
    }
}
