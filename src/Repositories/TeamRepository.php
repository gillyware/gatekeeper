<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Team\TeamNotFoundException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Team>
 */
class TeamRepository implements EntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
    ) {}

    /**
     * Check if the teams table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS));
    }

    /**
     * Check if a team with the given name exists.
     */
    public function exists(string $teamName): bool
    {
        return Team::query()->where('name', $teamName)->exists();
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string $teamName): ?Team
    {
        return $this->all()->get($teamName);
    }

    /**
     * Find a team by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $teamName): ?Team
    {
        return $this->forModel($model)->get($teamName);
    }

    /**
     * Find a team by its name, or fail.
     */
    public function findOrFailByName(string $teamName): Team
    {
        $team = $this->findByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        return $team;
    }

    /**
     * Create a new team.
     */
    public function create(string $teamName): Team
    {
        $team = new Team(['name' => $teamName]);

        if ($team->save()) {
            $this->cacheService->invalidateCacheForAllTeams();
        }

        return $team->fresh();
    }

    /**
     * Update an existing team.
     *
     * @param  Team  $team
     */
    public function update($team, string $newTeamName): Team
    {
        if ($team->update(['name' => $newTeamName])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Deactivate a team.
     *
     * @param  Team  $team
     */
    public function deactivate($team): Team
    {
        if ($team->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Reactivate a team.
     *
     * @param  Team  $team
     */
    public function reactivate($team): Team
    {
        if ($team->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Delete a team.
     *
     * @param  Team  $team
     */
    public function delete($team): bool
    {
        // Unassign all permissions and roles from the team (without audit logging).
        $this->modelHasPermissionRepository->deleteForModel($team);
        $this->modelHasRoleRepository->deleteForModel($team);

        $deleted = $team->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all teams.
     *
     * @return Collection<Team>
     */
    public function all(): Collection
    {
        $teams = $this->cacheService->getAllTeams();

        if ($teams) {
            return $teams;
        }

        $teams = Team::all()->mapWithKeys(fn (Team $t) => [$t->name => $t]);

        $this->cacheService->putAllTeams($teams);

        return $teams;
    }

    /**
     * Get all active teams.
     *
     * @return Collection<Team>
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Team $team) => $team->is_active);
    }

    /**
     * Get all teams where the name is in the provided array or collection.
     *
     * @return Collection<Team>
     */
    public function whereNameIn(array|Collection $teamNames): Collection
    {
        return $this->all()->whereIn('name', $teamNames);
    }

    /**
     * Get all team names for a specific model.
     *
     * @return Collection<string>
     */
    public function namesForModel(Model $model): Collection
    {
        $allTeamNames = $this->cacheService->getModelTeamNames($model);

        if ($allTeamNames) {
            return $allTeamNames;
        }

        $teamsTable = Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS);
        $modelHasTeamsTable = Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS);

        $allTeamNames = $model->teams()
            ->select("$teamsTable.*")
            ->whereNull("$modelHasTeamsTable.deleted_at")
            ->pluck("$teamsTable.name")
            ->values();

        $this->cacheService->putModelTeamNames($model, $allTeamNames);

        return $allTeamNames;
    }

    /**
     * Get all teams for a specific model.
     *
     * @return Collection<Team>
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active teams for a specific model.
     *
     * @return Collection<Team>
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Team $team) => $team->is_active);
    }

    /**
     * Get a page of teams.
     */
    public function getPage(EntityPagePacket $entityPagePacket): LengthAwarePaginator
    {
        $query = Team::query()->whereLike('name', "%{$entityPagePacket->searchTerm}%");

        if ($entityPagePacket->prioritizedAttribute === 'is_active') {
            $query = $query
                ->orderBy('is_active', $entityPagePacket->isActiveOrder)
                ->orderBy('name', $entityPagePacket->nameOrder);
        } else {
            $query = $query
                ->orderBy('name', $entityPagePacket->nameOrder)
                ->orderBy('is_active', $entityPagePacket->isActiveOrder);
        }

        return $query->paginate(10, ['*'], 'page', $entityPagePacket->page);
    }
}
