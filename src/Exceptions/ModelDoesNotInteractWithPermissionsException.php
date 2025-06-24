<?php

namespace Braxey\Gatekeeper\Exceptions;

class ModelDoesNotInteractWithPermissionsException extends \Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $modelClass)
    {
        parent::__construct("The model class [{$modelClass}] does not interact with permissions. Consider using the `Braxey\Gatekeeper\Traits\HasPermissions` trait in your model.");
    }
}
