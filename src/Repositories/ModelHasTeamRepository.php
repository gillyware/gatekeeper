<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Assign a team to a model.
     *
     * @param  Team  $team
     */
    public function assignToModel(Model $model, $team): ModelHasTeam
    {
        $modelHasTeam = ModelHasTeam::query()->updateOrCreate([
            'team_id' => $team->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => false,
        ]);

        $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($model);

        return $modelHasTeam;
    }

    /**
     * Delete all non-denied team assignments for a given model and team.
     *
     * @param  Team  $team
     */
    public function unassignFromModel(Model $model, $team): bool
    {
        ModelHasTeam::forModel($model)
            ->where('team_id', $team->id)
            ->where('denied', false)
            ->delete();

        $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($model);

        return true;
    }

    /**
     * Deny a team from a model.
     *
     * @param  Team  $team
     */
    public function denyFromModel(Model $model, $team): ModelHasTeam
    {
        $modelHasTeam = ModelHasTeam::query()->updateOrCreate([
            'team_id' => $team->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => true,
        ]);

        $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($model);

        return $modelHasTeam;
    }

    /**
     * Delete all denied team assignments for a given model and team.
     *
     * @param  Team  $team
     */
    public function undenyFromModel(Model $model, $team): bool
    {
        ModelHasTeam::forModel($model)
            ->where('team_id', $team->id)
            ->where('denied', true)
            ->delete();

        $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($model);

        return true;
    }

    /**
     * Delete all team assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasTeam::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($model);

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
                    $this->cacheService->invalidateCacheForModelTeamLinksAndAccess($modelHasTeam->model);
                }
            });

        return true;
    }

    /**
     * Search model team assignments by team name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasTeam::query()
            ->select((new ModelHasTeam)->qualifyColumn('*'))
            ->join((new Team)->getTable(), (new Team)->qualifyColumn('id'), '=', (new ModelHasTeam)->qualifyColumn('team_id'))
            ->forModel($model)
            ->where('denied', false)
            ->whereIn('team_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Team)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('team:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned teams by team name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return Team::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model) {
                $subquery->select('team_id')
                    ->from((new ModelHasTeam)->getTable())
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull((new ModelHasTeam)->qualifyColumn('deleted_at'));
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search denied teams by team name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasTeam::query()
            ->select((new ModelHasTeam)->qualifyColumn('*'))
            ->join((new Team)->getTable(), (new Team)->qualifyColumn('id'), '=', (new ModelHasTeam)->qualifyColumn('team_id'))
            ->forModel($model)
            ->where('denied', true)
            ->whereIn('team_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Team)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('team:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }
}
