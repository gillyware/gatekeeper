<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelTeamService
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasTeamRepository $modelHasTeamRepository,
    ) {}

    /**
     * Search model team assignments by team name.
     */
    public function searchAssignmentsByTeamNameForModel(Model $model, string $teamNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $paginator = $this->modelHasTeamRepository->searchAssignmentsByTeamNameForModel($model, $teamNameSearchTerm, $pageNumber);

        return $paginator->through(function (ModelHasTeam $modelHasTeam) {
            $modelHasTeam->offsetSet('assigned_at', $modelHasTeam->created_at->format('Y-m-d H:i:s T'));

            return $modelHasTeam;
        });
    }

    /**
     * Search unassigned teams by team name for model.
     */
    public function searchUnassignedByTeamNameForModel(Model $model, string $teamNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        return $this->modelHasTeamRepository->searchUnassignedByTeamNameForModel($model, $teamNameSearchTerm, $pageNumber);
    }
}
