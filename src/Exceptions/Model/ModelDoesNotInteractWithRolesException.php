<?php

namespace Gillyware\Gatekeeper\Exceptions\Model;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Illuminate\Database\Eloquent\Model;

class ModelDoesNotInteractWithRolesException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(Model $model)
    {
        $className = get_class($model);
        parent::__construct("The model class [{$className}] cannot have roles. Consider using the [Gillyware\Gatekeeper\Traits\HasRoles] trait in your model.");
    }
}
