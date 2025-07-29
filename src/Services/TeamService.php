<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\TeamSourceType;
use Gillyware\Gatekeeper\Exceptions\Team\TeamAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\AssignTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\CreateTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\DeactivateTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\DeleteTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\DenyTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\GrantedTeamByDefaultAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\ReactivateTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\RevokedTeamDefaultGrantAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\UnassignTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\UndenyTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Team\UpdateTeamAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\UpdateTeamPacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
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
            $this->auditLogRepository->create(CreateTeamAuditLogPacket::make($createdTeam));
        }

        return $createdTeam->toPacket();
    }

    /**
     * Update an existing team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     * @param UpdateTeamPacket
     */
    public function update($team, $packet): TeamPacket
    {
        return match ($packet->action) {
            EntityUpdateAction::Name->value => $this->updateName($team, $packet->value),
            EntityUpdateAction::Status->value => $packet->value ? $this->reactivate($team) : $this->deactivate($team),
            EntityUpdateAction::DefaultGrant->value => $packet->value ? $this->grantByDefault($team) : $this->revokeDefaultGrant($team),
            default => throw new InvalidArgumentException('Invalid update action.'),
        };
    }

    /**
     * Update an existing team name.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function updateName($team, string|UnitEnum $newTeamName): TeamPacket
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $newTeamName = $this->resolveEntityName($newTeamName);

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if ($this->exists($newTeamName) && $currentTeam->name !== $newTeamName) {
            throw new TeamAlreadyExistsException($newTeamName);
        }

        $oldTeamName = $currentTeam->name;
        $updatedTeam = $this->teamRepository->updateName($currentTeam, $newTeamName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdateTeamAuditLogPacket::make($updatedTeam, $oldTeamName));
        }

        return $updatedTeam->toPacket();
    }

    /**
     * Grant a team to all models that are not explicitly denying it.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function grantByDefault($team): TeamPacket
    {
        $this->enforceAuditFeature();
        $this->enforceTeamsFeature();

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if ($currentTeam->grant_by_default) {
            return $currentTeam->toPacket();
        }

        $defaultedOnTeam = $this->teamRepository->grantByDefault($currentTeam);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(GrantedTeamByDefaultAuditLogPacket::make($defaultedOnTeam));
        }

        return $defaultedOnTeam->toPacket();
    }

    /**
     * Revoke a team's default grant.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function revokeDefaultGrant($team): TeamPacket
    {
        $this->enforceAuditFeature();

        $currentTeam = $this->resolveEntity($team, orFail: true);

        if (! $currentTeam->grant_by_default) {
            return $currentTeam->toPacket();
        }

        $defaultedOffTeam = $this->teamRepository->revokeDefaultGrant($currentTeam);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokedTeamDefaultGrantAuditLogPacket::make($defaultedOffTeam));
        }

        return $defaultedOffTeam->toPacket();
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
            $this->auditLogRepository->create(DeactivateTeamAuditLogPacket::make($deactivatedTeam));
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
            $this->auditLogRepository->create(ReactivateTeamAuditLogPacket::make($reactivatedTeam));
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
            $this->auditLogRepository->create(DeleteTeamAuditLogPacket::make($team));
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

        $this->modelHasTeamRepository->assignToModel($model, $team);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(AssignTeamAuditLogPacket::make($model, $team));
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
     * Unassign a team from a model.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function unassignFromModel(Model $model, $team): bool
    {
        $this->enforceAuditFeature();

        $team = $this->resolveEntity($team, orFail: true);

        $removed = $this->modelHasTeamRepository->unassignFromModel($model, $team);

        if ($removed && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UnassignTeamAuditLogPacket::make($model, $team));
        }

        return $removed;
    }

    /**
     * Unassign multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function unassignAllFromModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->resolveEntities($teams, orFail: true)->each(function (Team $team) use ($model, &$result) {
            $result = $result && $this->unassignFromModel($model, $team);
        });

        return $result;
    }

    /**
     * Deny a team from a model.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function denyFromModel(Model $model, $team): bool
    {
        $this->enforceAuditFeature();

        $team = $this->resolveEntity($team, orFail: true);

        $denied = $this->modelHasTeamRepository->denyFromModel($model, $team);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DenyTeamAuditLogPacket::make($model, $team));
        }

        return (bool) $denied;
    }

    /**
     * Deny multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $features
     */
    public function denyAllFromModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->resolveEntities($teams, orFail: true)->each(function (Team $team) use ($model, &$result) {
            $result = $result && $this->denyFromModel($model, $team);
        });

        return $result;
    }

    /**
     * Undeny a team from a model.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function undenyFromModel(Model $model, $team): bool
    {
        $this->enforceTeamsFeature();
        $this->enforceAuditFeature();

        $team = $this->resolveEntity($team, orFail: true);

        $denied = $this->modelHasTeamRepository->undenyFromModel($model, $team);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UndenyTeamAuditLogPacket::make($model, $team));
        }

        return (bool) $denied;
    }

    /**
     * Deny multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $features
     */
    public function undenyAllFromModel(Model $model, array|Arrayable $teams): bool
    {
        $result = true;

        $this->resolveEntities($teams, orFail: true)->each(function (Team $team) use ($model, &$result) {
            $result = $result && $this->undenyFromModel($model, $team);
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

        // If the team is denied from the model, return false.
        if ($this->teamRepository->deniedFromModel($model)->has($team->name)) {
            return false;
        }

        // The team cannot be accessed if it does not exist or is inactive.
        if (! $team || ! $team->is_active) {
            return false;
        }

        // If the team is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $team)) {
            return true;
        }

        return $team->grant_by_default;
    }

    /**
     * Check if a model directly has the given team.
     *
     * @param  Team|TeamPacket|string|UnitEnum  $team
     */
    public function modelHasDirectly(Model $model, $team): bool
    {
        $team = $this->resolveEntity($team);

        if (! $team) {
            return false;
        }

        $foundAssignment = $this->teamRepository->assignedToModel($model)->get($team->name);

        return $foundAssignment && $foundAssignment->is_active;
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
     * @return Collection<string, TeamPacket>
     */
    public function getAll(): Collection
    {
        return $this->teamRepository->all()
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get all teams assigned directly or indirectly to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->teamRepository->all()
            ->filter(fn (Team $team) => $this->modelHas($model, $team))
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get all teams directly assigned to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->teamRepository->assignedToModel($model)
            ->map(fn (Team $team) => $team->toPacket());
    }

    /**
     * Get all effective teams for the given model with the team source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->teamsFeatureEnabled() || ! $this->modelInteractsWithTeams($model)) {
            return $result;
        }

        $this->teamRepository->all()
            ->filter(function (Team $team) use ($model) {
                $denied = $this->teamRepository->deniedFromModel($model)->has($team->name);

                return $team->grant_by_default && ! $denied;
            })
            ->each(function (Team $team) use (&$sourcesMap) {
                $sourcesMap[$team->name][] = [
                    'type' => TeamSourceType::DEFAULT,
                ];
            });

        $this->teamRepository->assignedToModel($model)
            ->filter(fn (Team $team) => $team->is_active)
            ->each(function (Team $team) use (&$sourcesMap) {
                $sourcesMap[$team->name][] = ['type' => TeamSourceType::DIRECT];
            });

        foreach ($sourcesMap as $roleName => $sources) {
            $result->push([
                'name' => $roleName,
                'sources' => $sources,
            ]);
        }

        return $result;
    }

    /**
     * Get a page of teams.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        return $this->teamRepository->getPage($packet)
            ->through(fn (Team $team) => $team->toPacket());
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
