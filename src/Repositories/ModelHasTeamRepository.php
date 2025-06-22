<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ModelHasTeamRepository
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a team is assigned to any model.
     */
    public function existsForTeam(Team $team): bool
    {
        return ModelHasTeam::query()->where('team_id', $team->id)->exists();
    }

    /**
     * Create a new ModelHasTeam instance.
     */
    public function create(Model $model, Team $team): ModelHasTeam
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
     * Get all ModelHasTeam instances for a given model and team.
     */
    public function getForModelAndTeam(Model $model, Team $team): Collection
    {
        return ModelHasTeam::forModel($model)->where('team_id', $team->id)->get();
    }

    /**
     * Get the most recent ModelHasTeam instance for a given model and team, including trashed instances.
     */
    public function getRecentForModelAndTeamIncludingTrashed(Model $model, Team $team): ?ModelHasTeam
    {
        return ModelHasTeam::forModel($model)
            ->where('team_id', $team->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    /**
     * Delete all ModelHasTeam instances for a given model and team.
     */
    public function deleteForModelAndTeam(Model $model, Team $team): bool
    {
        $this->getForModelAndTeam($model, $team)->each(function (ModelHasTeam $modelHasTeam) {
            $modelHasTeam->delete();
        });

        $this->cacheService->invalidateCacheForModelTeamNames($model);

        return true;
    }

    /**
     * Search for models by team name and model searchables.
     */
    public function searchByTeam(string $modelLabel, string $teamNameSearchTerm, string $modelSearchTerm, int $page): LengthAwarePaginator
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($modelLabel);
        $className = $this->modelMetadataService->getClassFromModelData($modelData);

        $searchableColumns = array_keys($modelData['searchable'] ?? []);
        $displayableColumns = array_keys($modelData['displayable'] ?? []);
        $primaryKey = (new $className)->getKeyName();

        $teamIds = Team::query()
            ->where('name', 'like', "%{$teamNameSearchTerm}%")
            ->pluck('id');

        $query = ModelHasTeam::query()
            ->where('model_type', $className)
            ->whereIn('team_id', $teamIds)
            ->whereHasMorph('model', $className, function ($query) use ($searchableColumns, $modelSearchTerm) {
                $query->where(function ($query) use ($searchableColumns, $modelSearchTerm) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($column, 'like', "%{$modelSearchTerm}%");
                    }
                });
            })
            ->with([
                'team:id,name,is_active',
                'model' => function ($query) use ($displayableColumns, $primaryKey) {
                    $query->select(array_merge([$primaryKey], $displayableColumns));
                },
            ]);

        return $query->paginate(10, ['*'], 'page', $page);
    }

    /**
     * Search model team assignments by team name.
     */
    public function searchAssignmentsByTeamNameForModel(Model $model, string $teamNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $teamsTable = Config::get('gatekeeper.tables.teams');
        $modelTeamsTable = Config::get('gatekeeper.tables.model_has_teams');

        $query = ModelHasTeam::query()
            ->select("$modelTeamsTable.*")
            ->join($teamsTable, "$teamsTable.id", '=', "$modelTeamsTable.team_id")
            ->forModel($model)
            ->whereIn('team_id', function ($sub) use ($teamsTable, $teamNameSearchTerm) {
                $sub->select('id')
                    ->from($teamsTable)
                    ->where('name', 'like', "%{$teamNameSearchTerm}%");
            })
            ->orderBy("$teamsTable.is_active")
            ->orderBy("$teamsTable.name")
            ->with('team:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }

    /**
     * Search unassigned teams by team name for model.
     */
    public function searchUnassignedByTeamNameForModel(Model $model, string $teamNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $modelTeamsTable = Config::get('gatekeeper.tables.model_has_teams');

        $query = Team::query()
            ->where('name', 'like', "%{$teamNameSearchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelTeamsTable) {
                $subquery->select('team_id')
                    ->from($modelTeamsTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelTeamsTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }
}
