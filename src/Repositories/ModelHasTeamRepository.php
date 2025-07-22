<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

/**
 * @implements ModelHasEntityRepositoryInterface<Team, ModelHasTeam>
 */
class ModelHasTeamRepository implements ModelHasEntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a team is assigned to any model.
     *
     * @param  Team  $team
     */
    public function existsForEntity($team): bool
    {
        return ModelHasTeam::query()->where('team_id', $team->id)->exists();
    }

    /**
     * Create a new model team assigment.
     *
     * @param  Team  $team
     */
    public function create(Model $model, $team): ModelHasTeam
    {
        $modelHasTeam = ModelHasTeam::create([
            'team_id' => $team->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);

        $this->cacheService->invalidateCacheForModelTeamNames($model);

        return $modelHasTeam;
    }

    /**
     * Delete all team assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasTeam::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelTeamNames($model);

        return true;
    }

    /**
     * Delete all assignments for a given team.
     *
     * @param  Team  $team
     */
    public function deleteForEntity($team): bool
    {
        ModelHasTeam::query()->where('team_id', $team->id)
            ->with('model')
            ->get()
            ->each(function (ModelHasTeam $modelHasTeam) {
                $modelHasTeam->delete();

                if ($modelHasTeam->model) {
                    $this->cacheService->invalidateCacheForModelTeamNames($modelHasTeam->model);
                }
            });

        return true;
    }

    /**
     * Delete all team assignments for a given model and team.
     *
     * @param  Team  $team
     */
    public function deleteForModelAndEntity(Model $model, $team): bool
    {
        ModelHasTeam::forModel($model)->where('team_id', $team->id)->delete();

        $this->cacheService->invalidateCacheForModelTeamNames($model);

        return true;
    }

    /**
     * Search model team assignments by team name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $teamsTable = Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS);
        $modelTeamsTable = Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS);

        $query = ModelHasTeam::query()
            ->select("$modelTeamsTable.*")
            ->join($teamsTable, "$teamsTable.id", '=', "$modelTeamsTable.team_id")
            ->forModel($model)
            ->whereIn('team_id', function ($sub) use ($teamsTable, $packet) {
                $sub->select('id')
                    ->from($teamsTable)
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc("$teamsTable.is_active")
            ->orderBy("$teamsTable.name")
            ->with('team:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned teams by team name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $modelTeamsTable = Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS);

        $query = Team::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelTeamsTable) {
                $subquery->select('team_id')
                    ->from($modelTeamsTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelTeamsTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }
}
