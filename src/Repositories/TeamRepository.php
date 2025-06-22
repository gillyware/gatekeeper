<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Exceptions\Team\TeamNotFoundException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class TeamRepository
{
    public function __construct(private readonly CacheService $cacheService) {}

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
        return $this->all()->firstWhere('name', $teamName);
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
     * Create a new Team instance.
     */
    public function create(string $teamName): Team
    {
        $team = new Team(['name' => $teamName]);

        if ($team->save()) {
            $this->cacheService->invalidateCacheForAllTeams();
        }

        return $team;
    }

    /**
     * Update an existing team.
     */
    public function update(Team $team, string $teamName): Team
    {
        if ($team->update(['name' => $teamName])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Deactivate a team.
     */
    public function deactivate(Team $team): Team
    {
        if ($team->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Reactivate a team.
     */
    public function reactivate(Team $team): Team
    {
        if ($team->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Delete a team.
     */
    public function delete(Team $team): bool
    {
        $deleted = $team->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all teams.
     */
    public function all(): Collection
    {
        $teams = $this->cacheService->getAllTeams();

        if ($teams) {
            return $teams;
        }

        $teams = Team::all()->values();

        $this->cacheService->putAllTeams($teams);

        return $teams;
    }

    /**
     * Get all active teams.
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Team $team) => $team->is_active)->values();
    }

    /**
     * Get all teams where the name is in the provided array or collection.
     */
    public function whereNameIn(array|Collection $teamNames): Collection
    {
        return $this->all()->whereIn('name', $teamNames)->values();
    }

    /**
     * Get all team names for a specific model.
     */
    public function namesForModel(Model $model): Collection
    {
        $allTeamNames = $this->cacheService->getModelTeamNames($model);

        if ($allTeamNames) {
            return $allTeamNames;
        }

        $teamsTable = Config::get('gatekeeper.tables.teams');
        $modelHasTeamsTable = Config::get('gatekeeper.tables.model_has_teams');

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
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active teams for a specific model.
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Team $team) => $team->is_active)
            ->values();
    }

    /**
     * Find a team by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $teamName): ?Team
    {
        return $this->forModel($model)->firstWhere('name', $teamName);
    }
}
