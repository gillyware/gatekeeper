<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

trait InteractsWithTeams
{
    /**
     * Get the active teams associated with the model.
     */
    public function getActiveTeamNames(): Collection
    {
        return $this->teamRepository()->getActiveNamesForModel($this);
    }

    /**
     * Get the teams associated with the model.
     */
    public function teams(): MorphToMany
    {
        $modelHasTeamsTable = Config::get('gatekeeper.tables.model_has_teams');

        return $this->morphToMany(Team::class, 'model', $modelHasTeamsTable, 'model_id', 'team_id');
    }

    /**
     * Get the team repository instance.
     */
    private function teamRepository(): TeamRepository
    {
        return app(TeamRepository::class);
    }
}
