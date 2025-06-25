<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Dtos\AuditLog\AssignTeamAuditLogDto;
use Braxey\Gatekeeper\Dtos\AuditLog\CreateTeamAuditLogDto;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\AuditLogRepository;
use Braxey\Gatekeeper\Repositories\ModelHasTeamRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class TeamService extends AbstractGatekeeperEntityService
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasTeamRepository $modelHasTeamRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    public function create(string $teamName): Team
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $team = $this->teamRepository->create($teamName);

        if (Config::get('gatekeeper.features.audit', true)) {
            $this->auditLogRepository->create(new CreateTeamAuditLogDto($team));
        }

        return $team;
    }

    /**
     * Assign a team to a model.
     */
    public function addModelTo(Model $model, Team|string $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();
        $this->enforceTeamInteraction($model);

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        // If the model already has this team directly assigned, we don't need to sync again.
        $directlyOnTeam = $this->modelDirectlyOnTeam($model, $team);

        if ($directlyOnTeam) {
            return true;
        }

        // Insert the team assignment.
        $this->modelHasTeamRepository->create($model, $team);

        // Audit log the team assignment if auditing is enabled.
        if (Config::get('gatekeeper.features.audit', true)) {
            $this->auditLogRepository->create(new AssignTeamAuditLogDto($model, $team));
        }

        // Invalidate the teams cache for the model.
        $this->teamRepository->invalidateCacheForModel($model);

        return true;
    }

    /**
     * Assign multiple teams to a model.
     */
    public function addModelToAll(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($teams) as $teamName) {
            $result = $result && $this->addModelTo($model, $teamName);
        }

        return $result;
    }

    /**
     * Revoke a team from a model.
     */
    public function removeModelFrom(Model $model, Team|string $team): bool
    {
        $this->resolveActingAs();
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();
        $this->enforceTeamInteraction($model);

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        if ($this->modelHasTeamRepository->deleteForModelAndTeam($model, $team)) {
            // Invalidate the teams cache for the model.
            $this->teamRepository->invalidateCacheForModel($model);

            return true;
        }

        return false;
    }

    /**
     * Revoke multiple teams from a model.
     */
    public function removeModelFromAll(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        foreach ($this->entityNamesArray($teams) as $teamName) {
            $result = $result && $this->removeModelFrom($model, $teamName);
        }

        return $result;
    }

    /**
     * Check if a model has a given team.
     */
    public function modelOn(Model $model, Team|string $team): bool
    {
        $this->enforceTeamsFeature();
        $this->enforceTeamInteraction($model);

        $teamName = $this->resolveEntityName($team);
        $team = $this->teamRepository->findByName($teamName);

        if (! $team->is_active) {
            return false;
        }

        // Check if the model has the team directly assigned.
        return $this->modelDirectlyOnTeam($model, $team);
    }

    /**
     * Check if a model has any of the given teams.
     */
    public function modelOnAny(Model $model, array|Arrayable $teams): bool
    {
        foreach ($this->entityNamesArray($teams) as $teamName) {
            if ($this->modelOn($model, $teamName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model has all of the given teams.
     */
    public function modelOnAll(Model $model, array|Arrayable $teams): bool
    {
        foreach ($this->entityNamesArray($teams) as $teamName) {
            if (! $this->modelOn($model, $teamName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a model has a team directly assigned.
     */
    private function modelDirectlyOnTeam(Model $model, Team $team): bool
    {
        // Check if the model has the team directly assigned.
        $recentTeamAssignment = $this->modelHasTeamRepository->getRecentForModelAndTeamIncludingTrashed($model, $team);

        // If the team is currently directly assigned to the model, return true.
        return $recentTeamAssignment && ! $recentTeamAssignment->deleted_at;
    }
}
