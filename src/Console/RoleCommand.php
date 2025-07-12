<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
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

        $this->entity = GatekeeperEntity::ROLE;
        $this->entityTable = Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                Action::ROLE_CREATE => $this->handleCreate(),
                Action::ROLE_UPDATE => $this->handleUpdate(),
                Action::ROLE_DEACTIVATE => $this->handleDeactivate(),
                Action::ROLE_REACTIVATE => $this->handleReactivate(),
                Action::ROLE_DELETE => $this->handleDelete(),
                Action::ROLE_ASSIGN => $this->handleAssign(),
                Action::ROLE_REVOKE => $this->handleRevoke(),
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
        $names = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $names->partition(function (string $name) {
            try {
                Gatekeeper::createRole($name);

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

        $role = $this->roleRepository->findOrFailByName($roleName);

        $newRoleName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity} name?");

        $this->resolveActor();

        Gatekeeper::updateRole($role, $newRoleName);

        info("Role '$roleName' updated successfully to '$newRoleName'.");
    }

    /**
     * Handle the deactivation of one or more active roles.
     */
    private function handleDeactivate(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $roles = $roleNames->map(
            fn (string $name) => $this->roleRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $roles->each(fn (Role $role) => Gatekeeper::deactivateRole($role));

        if ($roles->count() === 1) {
            info("Role '{$roleNames->first()}' deactivated successfully.");

            return;
        }

        $roleList = $roleNames->implode(', ');
        info("Roles '$roleList' deactivated successfully.");
    }

    /**
     * Handle the reactivation of one or more deactivated roles.
     */
    private function handleReactivate(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $roles = $roleNames->map(
            fn (string $name) => $this->roleRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $roles->each(fn (Role $role) => Gatekeeper::reactivateRole($role));

        if ($roles->count() === 1) {
            info("Role '{$roleNames->first()}' reactivated successfully.");

            return;
        }

        $roleList = $roleNames->implode(', ');
        info("Roles '$roleList' reactivated successfully.");
    }

    /**
     * Handle the deletion of one or more existing roles.
     */
    private function handleDelete(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $roles = $roleNames->map(
            fn (string $name) => $this->roleRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $roles->each(fn (Role $role) => Gatekeeper::deleteRole($role));

        if ($roles->count() === 1) {
            info("Role '{$roleNames->first()}' deleted successfully.");

            return;
        }

        $roleList = $roleNames->implode(', ');
        info("Roles '$roleList' deleted successfully.");
    }

    /**
     * Handle the assignment of one or more roles to a model.
     */
    private function handleAssign(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $roles = $roleNames->map(
            fn (string $name) => $this->roleRepository->findOrFailByName($name)
        );

        $actee = $this->gatherActee();

        $this->resolveActor();

        Gatekeeper::assignRolesToModel($actee, $roles);

        if ($roles->count() === 1) {
            info("Role '{$roleNames->first()}' assigned to model successfully.");

            return;
        }

        $roleList = $roleNames->implode(', ');
        info("Roles '$roleList' assigned to model successfully.");
    }

    /**
     * Handle the revocation of one or more roles from a model.
     */
    private function handleRevoke(): void
    {
        $roleNames = $this->gatherOneOrMoreExistingEntityNames();

        $roles = $roleNames->map(
            fn (string $name) => $this->roleRepository->findOrFailByName($name)
        );

        $actee = $this->gatherActee();

        $this->resolveActor();

        Gatekeeper::revokeRolesFromModel($actee, $roles);

        if ($roles->count() === 1) {
            info("Role '{$roleNames->first()}' revoked from model successfully.");

            return;
        }

        $roleList = $roleNames->implode(', ');
        info("Roles '$roleList' revoked from model successfully.");
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            Action::ROLE_CREATE => 'Create one or more new roles',
            Action::ROLE_UPDATE => 'Update an existing role',
            Action::ROLE_DEACTIVATE => 'Deactivate one or more active roles',
            Action::ROLE_REACTIVATE => 'Reactivate one or more inactive roles',
            Action::ROLE_DELETE => 'Delete one or more existing roles',
            Action::ROLE_ASSIGN => 'Assign one or more roles to a model',
            Action::ROLE_REVOKE => 'Revoke one or more roles from a model',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->roleRepository->all();

        $filtered = match ($this->action) {
            Action::ROLE_UPDATE, Action::ROLE_DELETE, Action::ROLE_ASSIGN, Action::ROLE_REVOKE => $all,
            Action::ROLE_DEACTIVATE => $all->filter(fn (Role $role) => $role->is_active),
            Action::ROLE_REACTIVATE => $all->filter(fn (Role $role) => ! $role->is_active),
            default => $all,
        };

        if ($filtered->isEmpty()) {
            throw new GatekeeperConsoleException(
                match ($this->action) {
                    Action::ROLE_UPDATE, Action::ROLE_DELETE, Action::ROLE_ASSIGN, Action::ROLE_REVOKE => 'No roles found.',
                    Action::ROLE_DEACTIVATE => 'No active roles found.',
                    Action::ROLE_REACTIVATE => 'No inactive roles found.',
                    default => 'No roles found.',
                });
        }

        return $filtered
            ->filter(fn (Role $role) => stripos($role->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
