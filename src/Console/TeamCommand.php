<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Illuminate\Support\Facades\Config;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class TeamCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:team';

    protected $description = 'Manage teams';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
        private readonly TeamRepository $teamRepository,
    ) {
        parent::__construct($modelService, $modelMetadataService);

        $this->entity = GatekeeperEntity::Team;
        $this->entityTable = Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS);
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                Action::TEAM_CREATE => $this->handleCreate(),
                Action::TEAM_UPDATE => $this->handleUpdate(),
                Action::TEAM_DEACTIVATE => $this->handleDeactivate(),
                Action::TEAM_REACTIVATE => $this->handleReactivate(),
                Action::TEAM_DELETE => $this->handleDelete(),
                Action::TEAM_ADD => $this->handleAdd(),
                Action::TEAM_REMOVE => $this->handleRemove(),
            };

            return self::SUCCESS;
        } catch (GatekeeperException $e) {
            error($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            $actionVerb = str($this->action)->after('_')->toString();
            error("An unexpected error occurred while trying to {$actionVerb} the team: {$e->getMessage()}");
        }

        return self::FAILURE;
    }

    /**
     * Handle the creation of one or more new teams.
     */
    private function handleCreate(): void
    {
        $teamNames = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity->value} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) {
            try {
                Gatekeeper::createTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' created successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to create team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the update of an existing team.
     */
    private function handleUpdate(): void
    {
        $teamName = $this->gatherOneExistingEntityName();

        $newTeamName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity->value} name?");

        $this->resolveActor();

        Gatekeeper::updateTeam($teamName, $newTeamName);

        info("Team '$teamName' updated successfully to '$newTeamName'.");
    }

    /**
     * Handle the deactivation of one or more active teams.
     */
    private function handleDeactivate(): void
    {
        $teamNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) {
            try {
                Gatekeeper::deactivateTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' deactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to deactivate team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the reactivation of one or more deactivated teams.
     */
    private function handleReactivate(): void
    {
        $teamNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) {
            try {
                Gatekeeper::reactivateTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' reactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to reactivate team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the deletion of one or more existing teams.
     */
    private function handleDelete(): void
    {
        $teamNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) {
            try {
                Gatekeeper::deleteTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' deleted successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to delete team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the addition of a model to one or more teams.
     */
    private function handleAdd(): void
    {
        $teamNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) use ($actee) {
            try {
                Gatekeeper::for($actee)->addToTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' assigned successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to assign team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the removal of a model from one or more teams.
     */
    private function handleRemove(): void
    {
        $teamNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $teamNames->partition(function (string $teamName) use ($actee) {
            try {
                Gatekeeper::for($actee)->removeFromTeam($teamName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Team{$plural} '{$successes->implode(', ')}' revoked successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to revoke team{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            Action::TEAM_CREATE => 'Create one or more new teams',
            Action::TEAM_UPDATE => 'Update an existing team',
            Action::TEAM_DEACTIVATE => 'Deactivate one or more active teams',
            Action::TEAM_REACTIVATE => 'Reactivate one or more inactive teams',
            Action::TEAM_DELETE => 'Delete one or more existing teams',
            Action::TEAM_ADD => 'Add a model to one or more teams',
            Action::TEAM_REMOVE => 'Remove a model from one or more teams',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->teamRepository->all();

        $filtered = match ($this->action) {
            Action::TEAM_UPDATE, Action::TEAM_DELETE, Action::TEAM_ADD, Action::TEAM_REMOVE => $all,
            Action::TEAM_DEACTIVATE => $all->filter(fn (Team $team) => $team->is_active),
            Action::TEAM_REACTIVATE => $all->filter(fn (Team $team) => ! $team->is_active),
            default => $all,
        };

        return $filtered
            ->filter(fn (Team $team) => stripos($team->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
