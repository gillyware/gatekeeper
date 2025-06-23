<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithTeams
{
    /**
     * Get the teams associated with the model.
     */
    public function teams(): MorphToMany
    {
        return $this->morphToMany(Team::class, 'model', 'model_has_teams', 'model_id', 'team_id');
    }
}
