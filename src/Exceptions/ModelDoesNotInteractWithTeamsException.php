<?php

namespace Braxey\Gatekeeper\Exceptions;

class ModelDoesNotInteractWithTeamsException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $modelClass)
    {
        parent::__construct("The model class [{$modelClass}] does not interact with teams. Consider using the `Braxey\Gatekeeper\Traits\HasTeams` trait in your model.");
    }
}
