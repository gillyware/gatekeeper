<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Dtos\AuditLog\Team\AssignTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\CreateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\DeactivateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\DeleteTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\ReactivateTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\RevokeTeamAuditLogDto;
use Gillyware\Gatekeeper\Dtos\AuditLog\Team\UpdateTeamAuditLogDto;
use Gillyware\Gatekeeper\Exceptions\Team\DeletingAssignedTeamException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TeamService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasTeamRepository $modelHasTeamRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if a team with the given name exists.
     */
    public function exists(string $teamName): bool
    {
        return $this->teamRepository->exists($teamName);
    }

    /**
     * Create a new team.
     */
    public function create(string $teamName): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

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
     */
    public function update(Team|string $team, string $newTeamName): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

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
     */
    public function deactivate(Team|string $team): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

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
     */
    public function reactivate(Team|string $team): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

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
     */
    public function delete(Team|string $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        if (! $team) {
            return true;
        }

        // If the team is currently assigned to any model, we cannot delete it.
        if ($this->modelHasTeamRepository->existsForTeam($team)) {
            throw new DeletingAssignedTeamException($teamName);
        }

        $deleted = $this->teamRepository->delete($team);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new DeleteTeamAuditLogDto($team));
        }

        return $deleted;
    }

    /**
     * Add a model to a team.
     */
    public function addModelTo(Model $model, Team|string $team): bool
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

        // If the model already has this team directly assigned, return true.
        if ($this->modelOnDirectly($model, $team)) {
            return true;
        }

        $this->modelHasTeamRepository->create($model, $team);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new AssignTeamAuditLogDto($model, $team));
        }

        return true;
    }

    /**
     * Add a model to multiple teams.
     */
    public function addModelToAll(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->entityNames($teams)->each(function (string $teamName) use ($model, &$result) {
            $result = $result && $this->addModelTo($model, $teamName);
        });

        return $result;
    }

    /**
     * Remove a model from a team.
     */
    public function removeModelFrom(Model $model, Team|string $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findOrFailByName($teamName);

        $removed = $this->modelHasTeamRepository->deleteForModelAndTeam($model, $team);

        if ($removed && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(new RevokeTeamAuditLogDto($model, $team));
        }

        return $removed;
    }

    /**
     * Remove a model from multiple teams.
     */
    public function removeModelFromAll(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->entityNames($teams)->each(function (string $teamName) use ($model, &$result) {
            $result = $result && $this->removeModelFrom($model, $teamName);
        });

        return $result;
    }

    /**
     * Check if a model is on a given team.
     */
    public function modelOn(Model $model, Team|string $team): bool
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

        return $this->modelOnDirectly($model, $team);
    }

    /**
     * Check if a model is directly assigned to a team.
     */
    public function modelOnDirectly(Model $model, Team $team): bool
    {
        return $this->teamRepository->activeForModel($model)->some(fn (Team $t) => $team->is($t));
    }

    /**
     * Check if a model is on any of the given teams.
     */
    public function modelOnAny(Model $model, array|Arrayable $teams): bool
    {
        return $this->entityNames($teams)->some(
            fn (string $teamName) => $this->modelOn($model, $teamName)
        );
    }

    /**
     * Check if a model is on all of the given teams.
     */
    public function modelOnAll(Model $model, array|Arrayable $teams): bool
    {
        return $this->entityNames($teams)->every(
            fn (string $teamName) => $this->modelOn($model, $teamName)
        );
    }

    /**
     * Find a team by its name.
     */
    public function findByName(string $teamName): ?Team
    {
        return $this->teamRepository->findByName($teamName);
    }

    /**
     * Get all teams.
     */
    public function getAll(): Collection
    {
        return $this->teamRepository->all();
    }

    /**
     * Get all teams directly assigned to a model.
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->teamRepository->forModel($model);
    }
}
