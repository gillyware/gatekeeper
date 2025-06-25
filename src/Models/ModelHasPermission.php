<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $permission_id
 *
 * @method static Builder<ModelHasPermission> forModel(Model $model)
 */
class ModelHasPermission extends AbstractModelHasGatekeeperEntity
{
    /**
     * The database table used by the model.
     */
    protected $table = 'model_has_permissions';

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->permission_id;
    }
}
