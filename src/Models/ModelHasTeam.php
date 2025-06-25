<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $team_id
 *
 * @method static Builder<ModelHasTeam> forModel(Model $model)
 */
class ModelHasTeam extends AbstractModelHasGatekeeperEntity
{
    /**
     * The database table used by the model.
     */
    protected $table = 'model_has_teams';

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->team_id;
    }
}
