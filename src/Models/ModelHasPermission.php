<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $permission_id
 *
 * @method static Builder<ModelHasPermission> forModel(Model $model)
 */
class ModelHasPermission extends AbstractModelHasGatekeeperEntity
{
    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.model_has_permissions');
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->permission_id;
    }
}
