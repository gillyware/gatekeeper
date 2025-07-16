<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

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
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasTeam $modelHasTeam) use ($displayTimezone) {
            $modelHasTeam->offsetSet('assigned_at', $modelHasTeam->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

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
