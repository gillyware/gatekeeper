<?php

namespace Gillyware\Gatekeeper\Exceptions\Feature;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class FeaturesFeatureDisabledException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct("The 'features' feature is disabled. Please enable it in the configuration.");
    }
}
