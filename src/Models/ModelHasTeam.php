<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $team_id
 *
 * @method static Builder<ModelHasTeam> forModel(Model $model)
 */
class ModelHasTeam extends AbstractModelHasGatekeeperEntity
{
    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.model_has_teams');
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->team_id;
    }
}
