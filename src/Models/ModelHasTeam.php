<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

/**
 * @property int $team_id
 *
 * @method static Builder<ModelHasTeam> forModel(Model $model)
 */
class ModelHasTeam extends AbstractModelHasGatekeeperEntity
{
    /**
     * Get the team associated with the model.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityIdAttribute(): int
    {
        return $this->team_id;
    }
}
