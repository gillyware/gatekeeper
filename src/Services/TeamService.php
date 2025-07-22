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
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Team, TeamPacket>
 */
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
        $teamName = $this->resolveEntityName($teamName);

        return $this->teamRepository->exists($teamName);
    }

    /**
     * Create a new team.
     */
    public function create(string|UnitEnum $teamName): TeamPacket
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $teamName = $this->resolveEntityName($teamName);

        if ($this->exists($teamName)) {
            throw new TeamAlreadyExistsException($teamName);
        }

        $createdTeam = $this->teamRepository->create($teamName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new CreateTeamAuditLogDto($createdTeam));
        }

        return $createdTeam->toPacket();
    }

    /**
     * Update an existing team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function update($team, string|UnitEnum $newTeamName): TeamPacket
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $newTeamName = $this->resolveEntityName($newTeamName);

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if ($this->exists($newTeamName) && $currentTeam->name !== $newTeamName) {
            throw new TeamAlreadyExistsException($newTeamName);
        }

        $oldTeamName = $currentTeam->name;
        $updatedTeam = $this->teamRepository->update($currentTeam, $newTeamName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new UpdateTeamAuditLogDto($updatedTeam, $oldTeamName));
        }

        return $updatedTeam->toPacket();
    }

    /**
     * Deactivate a team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function deactivate($team): TeamPacket
    {
        $this->enforceAuditFeature();

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if (! $currentTeam->is_active) {
            return $currentTeam->toPacket();
        }

        $deactivatedTeam = $this->teamRepository->deactivate($currentTeam);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeactivateTeamAuditLogDto($deactivatedTeam));
        }

        return $deactivatedTeam->toPacket();
    }

    /**
     * Reactivate a team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function reactivate($team): TeamPacket
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if ($currentTeam->is_active) {
            return $currentTeam->toPacket();
        }

        $reactivatedTeam = $this->teamRepository->reactivate($currentTeam);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new ReactivateTeamAuditLogDto($reactivatedTeam));
        }

        return $reactivatedTeam->toPacket();
    }

    /**
     * Delete a team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function delete($team): bool
    {
        $this->enforceAuditFeature();

        $team = $this->resolveEntity($team);

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
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function assignToModel(Model $model, $team): bool
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();
        $this->enforceTeamInteraction($model);
        $this->enforceModelIsNotTeam($model, 'Teams cannot be assigned to other teams');
        $this->enforceModelIsNotRole($model, 'Teams cannot be assigned to roles');
        $this->enforceModelIsNotPermission($model, 'Teams cannot be assigned to permissions');

        $team = $this->resolveEntity($team, orFail: true);

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
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function assignAllToModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->resolveEntities($teams, orFail: true)->each(function (Team $team) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $team);
        });

        return $result;
    }

    /**
     * Revoke a team from a model.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function revokeFromModel(Model $model, $team): bool
    {
        $this->enforceAuditFeature();

        $team = $this->resolveEntity($team, orFail: true);

        $removed = $this->modelHasTeamRepository->deleteForModelAndEntity($model, $team);

        if ($removed && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokeTeamAuditLogDto($model, $team));
        }

        return $removed;
    }

    /**
     * Revoke multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->resolveEntities($teams, orFail: true)->each(function (Team $team) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $team);
        });

        return $result;
    }

    /**
     * Check if a model has the given team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function modelHas(Model $model, $team): bool
    {
        // To access the team, the teams feature must be enabled and the model must be using the teams trait.
        if (! $this->teamsFeatureEnabled() || ! $this->modelInteractsWithTeams($model)) {
            return false;
        }

        $team = $this->resolveEntity($team);

        // The team cannot be accessed if it does not exist or is inactive.
        if (! $team || ! $team->is_active) {
            return false;
        }

        return $this->modelHasDirectly($model, $team);
    }

    /**
     * Check if a model directly has the given team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function modelHasDirectly(Model $model, $team): bool
    {
        $team = $this->resolveEntity($team);

        return $team && $this->teamRepository->activeForModel($model)->some(fn (Team $t) => $team->name === $t->name);
    }

    /**
     * Check if a model has any of the given teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function modelHasAny(Model $model, array|Arrayable $teams): bool
    {
        return $this->resolveEntities($teams)->filter()->some(
            fn (Team $team) => $this->modelHas($model, $team)
        );
    }

    /**
     * Check if a model has all of the given teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function modelHasAll(Model $model, array|Arrayable $teams): bool
    {
        return $this->resolveEntities($teams)->every(
            fn (?Team $team) => $team && $this->modelHas($model, $team)
        );
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string|UnitEnum $teamName): ?TeamPacket
    {
        return $this->resolveEntity($teamName)?->toPacket();
    }

    /**
     * Get all teams.
     *
     * @return Collection<TeamPacket>
     */
    public function getAll(): Collection
    {
        return $this->teamRepository->all()
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get all teams assigned directly or indirectly to a model.
     *
     * @return Collection<TeamPacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->getDirectForModel($model)
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get all teams directly assigned to a model.
     *
     * @return Collection<TeamPacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->teamRepository->forModel($model)
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get a page of teams.
     */
    public function getPage(EntityPagePacket $entityPagePacket): LengthAwarePaginator
    {
        return $this->teamRepository->getPage($entityPagePacket);
    }

    /**
     * Get the team model from the team or team name.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    protected function resolveEntity($team, bool $orFail = false): ?Team
    {
        if ($team instanceof Team) {
            return $team;
        }

        $teamName = $this->resolveEntityName($team);

        return $orFail
            ? $this->teamRepository->findOrFailByName($teamName)
            : $this->teamRepository->findByName($teamName);
    }
}
