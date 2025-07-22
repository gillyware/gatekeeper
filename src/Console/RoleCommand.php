<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperConsoleException;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Illuminate\Support\Facades\Config;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class RoleCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:role';

    protected $description = 'Manage roles';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
        private readonly RoleRepository $roleRepository,
    ) {
        parent::__construct($modelService, $modelMetadataService);

        $this->entity = GatekeeperEntity::Role;
        $this->entityTable = Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                AuditLogAction::CreateRole->value => $this->handleCreate(),
                AuditLogAction::UpdateRole->value => $this->handleUpdate(),
                AuditLogAction::DeactivateRole->value => $this->handleDeactivate(),
                AuditLogAction::ReactivateRole->value => $this->handleReactivate(),
                AuditLogAction::DeleteRole->value => $this->handleDelete(),
                AuditLogAction::AssignRole->value => $this->handleAssign(),
                AuditLogAction::RevokeRole->value => $this->handleRevoke(),
            };

            return self::SUCCESS;
        } catch (GatekeeperException $e) {
            error($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            $actionVerb = str($this->action)->after('_')->toString();
            error("An unexpected error occurred while trying to {$actionVerb} the role: {$e->getMessage()}");
        }

        return self::FAILURE;
    }

    /**
     * Handle the creation of one or more new roles.
     */
    private function handleCreate(): void
    {
        $roleNames = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity->value} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) {
            try {
                Gatekeeper::createRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' created successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to create role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the update of an existing role.
     */
    private function handleUpdate(): void
    {
        $roleName = $this->gatherOneExistingEntityName();

        $newRoleName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity->value} name?");

        $this->resolveActor();

        Gatekeeper::updateRole($roleName, $newRoleName);

        info("Role '$roleName' updated successfully to '$newRoleName'.");
    }

    /**
     * Handle the deactivation of one or more active roles.
     */
    private function handleDeactivate(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) {
            try {
                Gatekeeper::deactivateRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' deactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to deactivate role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the reactivation of one or more deactivated roles.
     */
    private function handleReactivate(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) {
            try {
                Gatekeeper::reactivateRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' reactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to reactivate role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the deletion of one or more existing roles.
     */
    private function handleDelete(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) {
            try {
                Gatekeeper::deleteRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' deleted successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to delete role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the assignment of one or more roles to a model.
     */
    private function handleAssign(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) use ($actee) {
            try {
                Gatekeeper::for($actee)->assignRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' assigned successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to assign role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the revocation of one or more roles from a model.
     */
    private function handleRevoke(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $roleNames->partition(function (string $roleName) use ($actee) {
            try {
                Gatekeeper::for($actee)->revokeRole($roleName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Role{$plural} '{$successes->implode(', ')}' revoked successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to revoke role{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            AuditLogAction::CreateRole->value => 'Create one or more new roles',
            AuditLogAction::UpdateRole->value => 'Update an existing role',
            AuditLogAction::DeactivateRole->value => 'Deactivate one or more active roles',
            AuditLogAction::ReactivateRole->value => 'Reactivate one or more inactive roles',
            AuditLogAction::DeleteRole->value => 'Delete one or more existing roles',
            AuditLogAction::AssignRole->value => 'Assign one or more roles to a model',
            AuditLogAction::RevokeRole->value => 'Revoke one or more roles from a model',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->roleRepository->all();

        $filtered = match ($this->action) {
            AuditLogAction::UpdateRole->value, AuditLogAction::DeleteRole->value, AuditLogAction::AssignRole->value, AuditLogAction::RevokeRole->value => $all,
            AuditLogAction::DeactivateRole->value => $all->filter(fn (Role $role) => $role->is_active),
            AuditLogAction::ReactivateRole->value => $all->filter(fn (Role $role) => ! $role->is_active),
            default => $all,
        };

        if ($filtered->isEmpty()) {
            throw new GatekeeperConsoleException(
                match ($this->action) {
                    AuditLogAction::UpdateRole->value, AuditLogAction::DeleteRole->value, AuditLogAction::AssignRole->value, AuditLogAction::RevokeRole->value => 'No roles found.',
                    AuditLogAction::DeactivateRole->value => 'No active roles found.',
                    AuditLogAction::ReactivateRole->value => 'No inactive roles found.',
                    default => 'No roles found.',
                });
        }

        return $filtered
            ->filter(fn (Role $role) => stripos($role->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
