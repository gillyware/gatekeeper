<?php

namespace Braxey\Gatekeeper\Exceptions;

class TeamNotFoundException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $teamName)
    {
        parent::__construct("Team '{$teamName}' not found.");
    }
}
