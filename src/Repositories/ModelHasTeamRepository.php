<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelHasTeamRepository
{
    public function create(Model $model, Team $team): ModelHasTeam
    {
        return ModelHasTeam::create([
            'team_id' => $team->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);
    }

    public function getForModelAndTeam(Model $model, Team $team): Collection
    {
        return ModelHasTeam::forModel($model)->where('team_id', $team->id)->get();
    }

    public function getRecentForModelAndTeamIncludingTrashed(Model $model, Team $team): ?ModelHasTeam
    {
        return ModelHasTeam::forModel($model)
            ->where('team_id', $team->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    public function deleteForModelAndTeam(Model $model, Team $team): bool
    {
        $this->getForModelAndTeam($model, $team)->each(function (ModelHasTeam $modelHasTeam) {
            $modelHasTeam->delete();
        });

        return true;
    }
}
