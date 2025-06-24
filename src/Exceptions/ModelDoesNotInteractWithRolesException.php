<?php

namespace Braxey\Gatekeeper\Exceptions;

class ModelDoesNotInteractWithRolesException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $modelClass)
    {
        parent::__construct("The model class [{$modelClass}] does not interact with roles. Consider using the `Braxey\Gatekeeper\Traits\HasRoles` trait in your model.");
    }
}
