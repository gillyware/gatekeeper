<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;

trait InteractsWithTeams
{
    /**
     * Get the teams associated with the model.
     */
    public function teams(): MorphToMany
    {
        $modelHasTeamsTable = Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS);

        return $this->morphToMany(Team::class, 'model', $modelHasTeamsTable, 'model_id', 'team_id');
    }
}
