<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Exceptions\TeamNotFoundException;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ItemNotFoundException;
use Throwable;

class TeamRepository
{
    public function __construct(private readonly CacheRepository $cacheRepository) {}

    /**
     * Create a new Team instance.
     */
    public function create(string $teamName): Team
    {
        $team = new Team(['name' => $teamName]);

        if ($team->save()) {
            $this->cacheRepository->forget($this->getCacheKeyForAll());
        }

        return $team;
    }

    /**
     * Get all teams.
     */
    public function all(): Collection
    {
        $teams = $this->cacheRepository->get($this->getCacheKeyForAll());

        if ($teams) {
            return collect($teams);
        }

        $teams = Team::all();

        $this->cacheRepository->put($this->getCacheKeyForAll(), $teams);

        return $teams;
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string $teamName): Team
    {
        try {
            return $this->all()->where('name', $teamName)->firstOrFail();
        } catch (ItemNotFoundException) {
            throw new TeamNotFoundException($teamName);
        } catch (Throwable $t) {
            throw $t;
        }
    }

    /**
     * Get all active teams.
     */
    public function getActive(): Collection
    {
        return $this->all()->filter(fn (Team $team) => $team->is_active);
    }

    /**
     * Get active teams where the name is in the provided array or collection.
     */
    public function getActiveWhereNameIn(array|Collection $teamNames): Collection
    {
        return $this->getActive()->whereIn('name', $teamNames);
    }

    /**
     * Get active teams for a specific model.
     */
    public function getActiveForModel(Model $model): Collection
    {
        $activeNamesForModel = $this->getActiveNamesForModel($model);

        return $this->getActiveWhereNameIn($activeNamesForModel);
    }

    /**
     * Get active team names for a specific model.
     */
    public function getActiveNamesForModel(Model $model): Collection
    {
        $cacheKey = $this->getCacheKeyForModel($model);

        $activeTeamNames = $this->cacheRepository->get($cacheKey);

        if ($activeTeamNames) {
            return collect($activeTeamNames);
        }

        $teamsTable = Config::get('gatekeeper.tables.teams');
        $modelHasTeamsTable = Config::get('gatekeeper.tables.model_has_teams');

        $activeTeamNames = $model->teams()
            ->select("$teamsTable.*")
            ->where('is_active', true)
            ->whereNull("$modelHasTeamsTable.deleted_at")
            ->pluck("$teamsTable.name");

        $this->cacheRepository->put($cacheKey, $activeTeamNames);

        return $activeTeamNames;
    }

    /**
     * Invalidate the cache for all teams.
     */
    public function invalidateCacheForModel(Model $model): void
    {
        $this->cacheRepository->forget($this->getCacheKeyForModel($model));
    }

    /**
     * Invalidate the cache for all teams.
     */
    private function getCacheKeyForAll(): string
    {
        return 'teams';
    }

    /**
     * Get the cache key for a specific model.
     */
    private function getCacheKeyForModel(Model $model): string
    {
        return "teams.{$model->getMorphClass()}.{$model->getKey()}";
    }
}
