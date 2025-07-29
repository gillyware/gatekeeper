<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithTeams
{
    /**
     * Get the teams associated with the model.
     */
    public function teams(): MorphToMany
    {
        $modelHasTeamsTable = (new ModelHasTeam)->getTable();

        return $this->morphToMany(Team::class, 'model', $modelHasTeamsTable, 'model_id', 'team_id');
    }
}
