<?php

namespace Gillyware\Gatekeeper\Exceptions\Model;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Illuminate\Database\Eloquent\Model;

class ModelDoesNotInteractWithTeamsException extends GatekeeperException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(Model $model)
    {
        $className = get_class($model);
        parent::__construct("The model class [{$className}] cannot join teams. Consider using the [Gillyware\Gatekeeper\Traits\HasTeams] trait in your model.");
    }
}
