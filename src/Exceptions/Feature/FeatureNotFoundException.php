<?php

namespace Gillyware\Gatekeeper\Exceptions\Feature;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;

class FeatureNotFoundException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $featureName)
    {
        parent::__construct("Feature '{$featureName}' not found.");
    }
}
