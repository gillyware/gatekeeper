<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Illuminate\Support\Facades\Config;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class PermissionCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:permission';

    protected $description = 'Manage permissions';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
        private readonly PermissionRepository $permissionRepository,
    ) {
        parent::__construct($modelService, $modelMetadataService);

        $this->entity = GatekeeperEntity::Permission;
        $this->entityTable = Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                AuditLogAction::CreatePermission->value => $this->handleCreate(),
                AuditLogAction::UpdatePermission->value => $this->handleUpdate(),
                AuditLogAction::DeactivatePermission->value => $this->handleDeactivate(),
                AuditLogAction::ReactivatePermission->value => $this->handleReactivate(),
                AuditLogAction::DeletePermission->value => $this->handleDelete(),
                AuditLogAction::AssignPermission->value => $this->handleAssign(),
                AuditLogAction::RevokePermission->value => $this->handleRevoke(),
            };

            return self::SUCCESS;
        } catch (GatekeeperException $e) {
            error($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            $actionVerb = str($this->action)->after('_')->toString();
            error("An unexpected error occurred while trying to {$actionVerb} the permission: {$e->getMessage()}");
        }

        return self::FAILURE;
    }

    /**
     * Handle the creation of one or more new permissions.
     */
    private function handleCreate(): void
    {
        $permissionNames = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity->value} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) {
            try {
                Gatekeeper::createPermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' created successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to create permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the update of an existing permission.
     */
    private function handleUpdate(): void
    {
        $permissionName = $this->gatherOneExistingEntityName();

        $newPermissionName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity->value} name?");

        $this->resolveActor();

        Gatekeeper::updatePermission($permissionName, $newPermissionName);

        info("Permission '$permissionName' updated successfully to '$newPermissionName'.");
    }

    /**
     * Handle the deactivation of one or more active permissions.
     */
    private function handleDeactivate(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) {
            try {
                Gatekeeper::deactivatePermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' deactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to deactivate permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the reactivation of one or more deactivated permissions.
     */
    private function handleReactivate(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) {
            try {
                Gatekeeper::reactivatePermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' reactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to reactivate permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the deletion of one or more existing permissions.
     */
    private function handleDelete(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) {
            try {
                Gatekeeper::deletePermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' deleted successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to delete permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the assignment of one or more permissions to a model.
     */
    private function handleAssign(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) use ($actee) {
            try {
                Gatekeeper::for($actee)->assignPermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' assigned successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to assign permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the revocation of one or more permissions from a model.
     */
    private function handleRevoke(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $permissionNames->partition(function (string $permissionName) use ($actee) {
            try {
                Gatekeeper::for($actee)->revokePermission($permissionName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Permission{$plural} '{$successes->implode(', ')}' revoked successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to revoke permission{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            AuditLogAction::CreatePermission->value => 'Create one or more new permissions',
            AuditLogAction::UpdatePermission->value => 'Update an existing permission',
            AuditLogAction::DeactivatePermission->value => 'Deactivate one or more active permissions',
            AuditLogAction::ReactivatePermission->value => 'Reactivate one or more inactive permissions',
            AuditLogAction::DeletePermission->value => 'Delete one or more existing permissions',
            AuditLogAction::AssignPermission->value => 'Assign one or more permissions to a model',
            AuditLogAction::RevokePermission->value => 'Revoke one or more permissions from a model',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->permissionRepository->all();

        $filtered = match ($this->action) {
            AuditLogAction::UpdatePermission->value, AuditLogAction::DeletePermission->value, AuditLogAction::AssignPermission->value, AuditLogAction::RevokePermission->value => $all,
            AuditLogAction::DeactivatePermission->value => $all->filter(fn (Permission $permission) => $permission->is_active),
            AuditLogAction::ReactivatePermission->value => $all->filter(fn (Permission $permission) => ! $permission->is_active),
            default => $all,
        };

        return $filtered
            ->filter(fn (Permission $permission) => stripos($permission->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
