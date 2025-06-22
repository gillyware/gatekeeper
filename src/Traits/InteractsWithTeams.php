<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\Team;

trait InteractsWithTeams
{
    /**
     * Get the teams associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function teams()
    {
        return $this->morphToMany(Team::class, 'model', 'model_has_teams', 'model_id', 'team_id')->withTrashed();
    }
}
