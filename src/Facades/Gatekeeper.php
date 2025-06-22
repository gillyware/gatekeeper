<?php

namespace Braxey\Gatekeeper\Facades;

use Illuminate\Support\Facades\Facade;

class Gatekeeper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gatekeeper';
    }
}
