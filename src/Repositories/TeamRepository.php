<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Team\TeamNotFoundException;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Team>
 */
class TeamRepository implements EntityRepositoryInterface
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
        private readonly ModelHasFeatureRepository $modelHasFeatureRepository,
    ) {}

    /**
     * Check if the teams table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable((new Team)->getTable());
    }

    /**
     * Check if a team with the given name exists.
     */
    public function exists(string $teamName): bool
    {
        return Team::query()->where('name', $teamName)->exists();
    }

    /**
     * Get all teams.
     *
     * @return Collection<string, Team>
     */
    public function all(): Collection
    {
        $teams = $this->cacheService->getAllTeams();

        if ($teams) {
            return $teams;
        }

        $teams = Team::all()->mapWithKeys(fn (Team $team) => [$team->name => $team]);

        $this->cacheService->putAllTeams($teams);

        return $teams;
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string $teamName): ?Team
    {
        return $this->all()->get($teamName);
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
     * Update an existing team name.
     *
     * @param  Team  $team
     */
    public function updateName($team, string $newTeamName): Team
    {
        if ($team->update(['name' => $newTeamName])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Grant a team to all models that are not explicitly denying it.
     *
     * @param  Team  $team
     */
    public function grantByDefault($team): Team
    {
        if ($team->update(['grant_by_default' => true])) {
            $this->cacheService->clear();
        }

        return $team;
    }

    /**
     * Revoke a team's default grant.
     *
     * @param  Team  $team
     */
    public function revokeDefaultGrant($team): Team
    {
        if ($team->update(['grant_by_default' => false])) {
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
        // Unassign all permissions, roles, and features from the team (without audit logging).
        $this->modelHasPermissionRepository->deleteForModel($team);
        $this->modelHasRoleRepository->deleteForModel($team);
        $this->modelHasFeatureRepository->deleteForModel($team);

        $deleted = $team->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all teams a specific model is on.
     *
     * @return Collection<string, Team>
     */
    public function assignedToModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => ! $link['denied'])
            ->map(fn (array $link) => $link['team']);
    }

    /**
     * Get all teams denied from a specific model.
     *
     * @return Collection<string, Team>
     */
    public function deniedFromModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => $link['denied'])
            ->map(fn (array $link) => $link['team']);
    }

    /**
     * Get a page of teams.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Team::query()->whereLike('name', "%{$packet->searchTerm}%");

        $query = match ($packet->prioritizedAttribute) {
            'name' => $query
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            'grant_by_default' => $query
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder),
            'is_active' => $query
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            default => $query,
        };

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Get all teams for a specific model.
     *
     * @return Collection<string, array{team: Team, denied: bool}>
     */
    private function forModel(Model $model): Collection
    {
        return $this->linksForModel($model)
            ->mapWithKeys(function (array $link) {
                [$name, $denied] = [$link['name'], $link['denied']];

                return [
                    $name => [
                        'team' => $this->findByName($name),
                        'denied' => $denied,
                    ],
                ];
            });
    }

    /**
     * Get all team names for a specific model.
     *
     * @return Collection<int, array{name: string, denied: bool}>
     */
    private function linksForModel(Model $model): Collection
    {
        $allTeamLinks = $this->cacheService->getModelTeamLinks($model);

        if ($allTeamLinks) {
            return $allTeamLinks;
        }

        if (! $this->modelInteractsWithTeams($model)) {
            return collect();
        }

        $allTeamLinks = $model->teams()
            ->select([
                'name' => (new Team)->qualifyColumn('name'),
                'denied' => (new ModelHasTeam)->qualifyColumn('denied'),
            ])
            ->whereNull((new ModelHasTeam)->qualifyColumn('deleted_at'))
            ->get(['name', 'denied'])
            ->map(fn (Team $team) => $team->only(['name', 'denied']));

        $this->cacheService->putModelTeamLinks($model, $allTeamLinks);

        return $allTeamLinks;
    }
}
