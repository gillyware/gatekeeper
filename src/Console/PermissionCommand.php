<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
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

        $this->entity = GatekeeperEntity::PERMISSION;
        $this->entityTable = Config::get('gatekeeper.tables.permissions');
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                Action::PERMISSION_CREATE => $this->handleCreate(),
                Action::PERMISSION_UPDATE => $this->handleUpdate(),
                Action::PERMISSION_DEACTIVATE => $this->handleDeactivate(),
                Action::PERMISSION_REACTIVATE => $this->handleReactivate(),
                Action::PERMISSION_DELETE => $this->handleDelete(),
                Action::PERMISSION_ASSIGN => $this->handleAssign(),
                Action::PERMISSION_REVOKE => $this->handleRevoke(),
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
        $names = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $names->partition(function (string $name) {
            try {
                Gatekeeper::createPermission($name);

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

        $permission = $this->permissionRepository->findOrFailByName($permissionName);

        $newPermissionName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity} name?");

        $this->resolveActor();

        Gatekeeper::updatePermission($permission, $newPermissionName);

        info("Permission '$permissionName' updated successfully to '$newPermissionName'.");
    }

    /**
     * Handle the deactivation of one or more active permissions.
     */
    private function handleDeactivate(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $permissions = $permissionNames->map(
            fn (string $name) => $this->permissionRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $permissions->each(fn (Permission $permission) => Gatekeeper::deactivatePermission($permission));

        if ($permissions->count() === 1) {
            info("Permission '{$permissionNames->first()}' deactivated successfully.");

            return;
        }

        $permissionList = $permissionNames->implode(', ');
        info("Permissions '$permissionList' deactivated successfully.");
    }

    /**
     * Handle the reactivation of one or more deactivated permissions.
     */
    private function handleReactivate(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $permissions = $permissionNames->map(
            fn (string $name) => $this->permissionRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $permissions->each(fn (Permission $permission) => Gatekeeper::reactivatePermission($permission));

        if ($permissions->count() === 1) {
            info("Permission '{$permissionNames->first()}' reactivated successfully.");

            return;
        }

        $permissionList = $permissionNames->implode(', ');
        info("Permissions '$permissionList' reactivated successfully.");
    }

    /**
     * Handle the deletion of one or more existing permissions.
     */
    private function handleDelete(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $permissions = $permissionNames->map(
            fn (string $name) => $this->permissionRepository->findOrFailByName($name)
        );

        $this->resolveActor();

        $permissions->each(fn (Permission $permission) => Gatekeeper::deletePermission($permission));

        if ($permissions->count() === 1) {
            info("Permission '{$permissionNames->first()}' deleted successfully.");

            return;
        }

        $permissionList = $permissionNames->implode(', ');
        info("Permissions '$permissionList' deleted successfully.");
    }

    /**
     * Handle the assignment of one or more permissions to a model.
     */
    private function handleAssign(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $permissions = $permissionNames->map(
            fn (string $name) => $this->permissionRepository->findOrFailByName($name)
        );

        $actee = $this->gatherActee();

        $this->resolveActor();

        Gatekeeper::assignPermissionsToModel($actee, $permissions);

        if ($permissions->count() === 1) {
            info("Permission '{$permissionNames->first()}' assigned to model successfully.");

            return;
        }

        $permissionList = $permissionNames->implode(', ');
        info("Permissions '$permissionList' assigned to model successfully.");
    }

    /**
     * Handle the revocation of one or more permissions from a model.
     */
    private function handleRevoke(): void
    {
        $permissionNames = $this->gatherOneOrMoreExistingEntityNames();

        $permissions = $permissionNames->map(
            fn (string $name) => $this->permissionRepository->findOrFailByName($name)
        );

        $actee = $this->gatherActee();

        $this->resolveActor();

        Gatekeeper::revokePermissionsFromModel($actee, $permissions);

        if ($permissions->count() === 1) {
            info("Permission '{$permissionNames->first()}' revoked from model successfully.");

            return;
        }

        $permissionList = $permissionNames->implode(', ');
        info("Permissions '$permissionList' revoked from model successfully.");
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            Action::PERMISSION_CREATE => 'Create one or more new permissions',
            Action::PERMISSION_UPDATE => 'Update an existing permission',
            Action::PERMISSION_DEACTIVATE => 'Deactivate one or more active permissions',
            Action::PERMISSION_REACTIVATE => 'Reactivate one or more inactive permissions',
            Action::PERMISSION_DELETE => 'Delete one or more existing permissions',
            Action::PERMISSION_ASSIGN => 'Assign one or more permissions to a model',
            Action::PERMISSION_REVOKE => 'Revoke one or more permissions from a model',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->permissionRepository->all();

        $filtered = match ($this->action) {
            Action::PERMISSION_UPDATE, Action::PERMISSION_DELETE, Action::PERMISSION_ASSIGN, Action::PERMISSION_REVOKE => $all,
            Action::PERMISSION_DEACTIVATE => $all->filter(fn (Permission $permission) => $permission->is_active),
            Action::PERMISSION_REACTIVATE => $all->filter(fn (Permission $permission) => ! $permission->is_active),
            default => $all,
        };

        return $filtered
            ->filter(fn (Permission $permission) => stripos($permission->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
