<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Dtos\AuditLog\Team\AssignTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\CreateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\DeactivateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\DeleteTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\ReactivateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\RevokeTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\UpdateTeamAuditLogDto;
use Gillyware\Gatekeeper\Exceptions\Team\TeamAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamNotFoundException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use UnitEnum;

class TeamService extends AbstractBaseEntityService
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasTeamRepository $modelHasTeamRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if the teams table exists.
     */
    public function tableExists(): bool
    {
        return $this->teamRepository->tableExists();
    }

    /**
     * Check if a team with the given name exists.
     */
    public function exists(string|UnitEnum $teamName): bool
    {
        return $this->teamRepository->exists($this->resolveEntityName($teamName));
    }

    /**
     * Create a new team.
     */
    public function create(string|UnitEnum $teamName): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $teamName = $this->resolveEntityName($teamName);

        if ($this->exists($teamName)) {
            throw new TeamAlreadyExistsException($teamName);
        }

        $team = $this->teamRepository->create($teamName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new CreateTeamAuditLogDto($team));
        }

        return $team;
    }

    /**
     * Update an existing team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function update($team, string|UnitEnum $newTeamName): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $newTeamName = $this->resolveEntityName($newTeamName);

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        if ($this->exists($newTeamName) && $team->name !== $newTeamName) {
            throw new TeamAlreadyExistsException($newTeamName);
        }

        $oldTeamName = $team->name;
        $team = $this->teamRepository->update($team, $newTeamName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new UpdateTeamAuditLogDto($team, $oldTeamName));
        }

        return $team;
    }

    /**
     * Deactivate a team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function deactivate($team): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        if (! $team->is_active) {
            return $team;
        }

        $team = $this->teamRepository->deactivate($team);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeactivateTeamAuditLogDto($team));
        }

        return $team;
    }

    /**
     * Reactivate a team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function reactivate($team): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        if ($team->is_active) {
            return $team;
        }

        $team = $this->teamRepository->reactivate($team);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new ReactivateTeamAuditLogDto($team));
        }

        return $team;
    }

    /**
     * Delete a team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function delete($team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        if (! $team) {
            return true;
        }

        // Delete any existing assignments for the team being deleted.
        if ($this->modelHasTeamRepository->existsForEntity($team)) {
            $this->modelHasTeamRepository->deleteForEntity($team);
        }

        $deleted = $this->teamRepository->delete($team);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeleteTeamAuditLogDto($team));
        }

        return (bool) $deleted;
    }

    /**
     * Assign a team to a model.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function assignToModel(Model $model, $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();
        $this->enforceTeamInteraction($model);
        $this->enforceModelIsNotTeam($model, 'Teams cannot be assigned to other teams');
        $this->enforceModelIsNotRole($model, 'Teams cannot be assigned to roles');
        $this->enforceModelIsNotPermission($model, 'Teams cannot be assigned to permissions');

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        // If the model already has this team directly assigned, return true.
        if ($this->modelHasDirectly($model, $team)) {
            return true;
        }

        $this->modelHasTeamRepository->create($model, $team);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new AssignTeamAuditLogDto($model, $team));
        }

        return true;
    }

    /**
     * Assign multiple teams to a model.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function assignAllToModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->entityNames($teams)->each(function (string $teamName) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $teamName);
        });

        return $result;
    }

    /**
     * Revoke a team from a model.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function revokeFromModel(Model $model, $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        if (! $team) {
            throw new TeamNotFoundException($teamName);
        }

        $removed = $this->modelHasTeamRepository->deleteForModelAndEntity($model, $team);

        if ($removed && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokeTeamAuditLogDto($model, $team));
        }

        return $removed;
    }

    /**
     * Revoke multiple teams from a model.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->entityNames($teams)->each(function (string $teamName) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $teamName);
        });

        return $result;
    }

    /**
     * Check if a model has the given team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function modelHas(Model $model, $team): bool
    {
        // To access the team, the teams feature must be enabled and the model must be using the teams trait.
        if (! $this->teamsFeatureEnabled() || ! $this->modelInteractsWithTeams($model)) {
            return false;
        }

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        // The team cannot be accessed if it does not exist or is inactive.
        if (! $team || ! $team->is_active) {
            return false;
        }

        return $this->modelHasDirectly($model, $team);
    }

    /**
     * Check if a model directly has the given team.
     *
     * @param  Team|string|UnitEnum  $team
     */
    public function modelHasDirectly(Model $model, $team): bool
    {
        $teamName = $this->resolveEntityName($team);

        return $this->teamRepository->activeForModel($model)->some(fn (Team $t) => $teamName === $t->name);
    }

    /**
     * Check if a model has any of the given teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function modelHasAny(Model $model, array|Arrayable $teams): bool
    {
        return $this->entityNames($teams)->some(
            fn (string $teamName) => $this->modelHas($model, $teamName)
        );
    }

    /**
     * Check if a model has all of the given teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function modelHasAll(Model $model, array|Arrayable $teams): bool
    {
        return $this->entityNames($teams)->every(
            fn (string $teamName) => $this->modelHas($model, $teamName)
        );
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string|UnitEnum $teamName): ?Team
    {
        return $this->teamRepository->findByName($this->resolveEntityName($teamName));
    }

    /**
     * Get all teams.
     */
    public function getAll(): Collection
    {
        return $this->teamRepository->all();
    }

    /**
     * Get all teams assigned directly or indirectly to a model.
     *
     * @return Collection<Team>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->getDirectForModel($model);
    }

    /**
     * Get all teams directly assigned to a model.
     *
     * @return Collection<Team>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->teamRepository->forModel($model);
    }

    /**
     * Get a page of permissions.
     */
    public function getPage(int $pageNumber, string $searchTerm, string $importantAttribute, string $nameOrder, string $isActiveOrder): LengthAwarePaginator
    {
        return $this->teamRepository->getPage($pageNumber, $searchTerm, $importantAttribute, $nameOrder, $isActiveOrder);
    }
}
