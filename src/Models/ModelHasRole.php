<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $role_id
 *
 * @method static Builder<ModelHasRole> forModel(Model $model)
 */
class ModelHasRole extends AbstractModelHasGatekeeperEntity
{
    /**
     * The database table used by the model.
     */
    protected $table = 'model_has_roles';

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->role_id;
    }
}
