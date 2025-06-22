<?php

namespace Gillyware\Gatekeeper\Exceptions\Model;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Illuminate\Database\Eloquent\Model;

class ModelDoesNotInteractWithPermissionsException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(Model $model)
    {
        $className = get_class($model);
        parent::__construct("The model class [{$className}] cannot have permissions. Consider using the [Gillyware\Gatekeeper\Traits\HasPermissions] trait in your model.");
    }
}
