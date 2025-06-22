<?php

namespace Gillyware\Gatekeeper\Exceptions\Model;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class MissingActingAsModelException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'audit' feature is enabled, but no acting as model is set.");
    }
}
