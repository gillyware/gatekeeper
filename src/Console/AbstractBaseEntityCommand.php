<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Enums\AuditLogActionVerb;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperConsoleException;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Factories\EntityServiceFactory;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Gatekeeper\Packets\Models\ModelPagePacket;
use Gillyware\Gatekeeper\Services\AbstractBaseEntityService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

abstract class AbstractBaseEntityCommand extends AbstractBaseGatekeeperCommand
{
    use EnforcesForGatekeeper;

    protected GatekeeperEntity $entity;

    protected string $entityTable;

    protected string $action;

    protected AbstractBaseEntityService $entityService;

    public function __construct(
        private readonly ModelService $modelService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {
        parent::__construct();

        $this->entityService = EntityServiceFactory::create($this->entity);
    }

    public function handle(): int
    {
        $this->clearTerminal();

        $this->action = $this->gatherEntityAction();

        try {
            match ($this->action) {
                AuditLogAction::build($this->entity, AuditLogActionVerb::Create)->value => $this->handleCreate(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::UpdateName)->value => $this->handleUpdateName(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::GrantByDefault)->value => $this->handleGrantByDefault(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::RevokeDefaultGrant)->value => $this->handleRevokeDefaultGrant(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Deactivate)->value => $this->handleDeactivate(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Reactivate)->value => $this->handleReactivate(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Delete)->value => $this->handleDelete(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Assign)->value => $this->handleAssign(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Unassign)->value => $this->handleUnassign(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Deny)->value => $this->handleDeny(),
                AuditLogAction::build($this->entity, AuditLogActionVerb::Undeny)->value => $this->handleUndeny(),
            };
        } catch (GatekeeperException $e) {
            error($e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            report($e);

            $actionVerb = str($this->action)->after('_')->toString();
            error("An unexpected error occurred while trying to [{$actionVerb}] the {$this->entity->value}: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::FAILURE;
    }

    /**
     * Handle the creation of one or more new entities.
     */
    private function handleCreate(): void
    {
        $entityNames = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity->value} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->create($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully created {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to create {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the update of an existing entity name.
     */
    private function handleUpdateName(): void
    {
        try {
            $entityName = $this->gatherOneExistingEntityName();

            $newEntityName = $this->gatherOneNonExistingEntityName("What will the new {$this->entity->value} name be?");

            $this->resolveActor();

            $this->entityService->updateName($entityName, $newEntityName);
        } catch (GatekeeperException $e) {
            error($e->getMessage());
        }

        info("Successfully updated {$this->entity->value} name from '{$entityName}' to '{$newEntityName}'");
    }

    /**
     * Handle granting one or more entities by default.
     */
    private function handleGrantByDefault(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->grantByDefault($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully granted {$this->entity->value}(s) by default: '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to grant {$this->entity->value}(s) by default: '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the revoking one or more default entity grants.
     */
    private function handleRevokeDefaultGrant(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->revokeDefaultGrant($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully revoked default grant for {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to revoke default grant for {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the deactivation of one or more active entities.
     */
    private function handleDeactivate(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->deactivate($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully deactivated {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to deactivate {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the reactivation of one or more deactivated entities.
     */
    private function handleReactivate(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->reactivate($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully reactivated {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to reactivate {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the deletion of one or more existing entities.
     */
    private function handleDelete(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) {
            try {
                $this->entityService->delete($entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully deleted {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to delete {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the assignment of one or more entities to a model.
     */
    private function handleAssign(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) use ($actee) {
            try {
                $this->entityService->assignToModel($actee, $entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully assigned {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to assign {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the unassignment of one or more entities from a model.
     */
    private function handleUnassign(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) use ($actee) {
            try {
                $this->entityService->unassignFromModel($actee, $entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully unassigned {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to unassign {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the denial of one or more entities from a model.
     */
    private function handleDeny(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) use ($actee) {
            try {
                $this->entityService->denyFromModel($actee, $entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully denied {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to deny {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Handle the undenial of one or more entities from a model.
     */
    private function handleUndeny(): void
    {
        $entityNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $entityNames->partition(function (string $entityName) use ($actee) {
            try {
                $this->entityService->undenyFromModel($actee, $entityName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            info("Successfully undenied {$this->entity->value}(s): '{$successes->implode("', '")}'.");
        }

        if ($failures->isNotEmpty()) {
            error("Failed to undeny {$this->entity->value}(s): '{$failures->implode("', '")}'.");
        }
    }

    /**
     * Gather the action to perform on an entity.
     */
    private function gatherEntityAction(): string
    {
        return select(
            label: 'What action do you want to perform?',
            options: $this->getActionOptions(),
            required: 'An action is required.',
            validate: ['string', Rule::in(array_keys($this->getActionOptions()))],
            default: array_key_first($this->getActionOptions()),
            scroll: 20,
        );
    }

    /**
     * Gather one existing entity name.
     */
    private function gatherOneExistingEntityName(): string
    {
        $actionVerb = str($this->action)->after('_')->toString();

        return search(
            label: "Search for the {$this->entity->value} to [$actionVerb]",
            options: fn (string $value) => $this->filterEntityNamesForAction($value),
            required: "A {$this->entity->value} name is required.",
            validate: ['string', 'max:255', Rule::exists($this->entityTable, 'name')],
            scroll: 10,
        );
    }

    /**
     * Gather one or more existing entity names.
     */
    private function gatherOneOrMoreExistingEntityNames(): Collection
    {
        $actionVerb = str($this->action)->after('_')->toString();

        return collect(multisearch(
            label: "Search for the {$this->entity->value}(s) to [$actionVerb]",
            options: fn (string $value) => $this->filterEntityNamesForAction($value),
            required: "At least one {$this->entity->value} name is required.",
            validate: ['array', 'min:1', 'max:100', Rule::exists($this->entityTable, 'name')],
            scroll: 10,
            hint: 'Select options with the space bar and confirm with enter.',
        ));
    }

    /**
     * Gather one non-existing entity name.
     */
    private function gatherOneNonExistingEntityName(string $label): string
    {
        return text(
            label: $label,
            required: "A {$this->entity->value} name is required.",
            validate: ['string', 'max:255', Rule::unique($this->entityTable, 'name')->withoutTrashed()],
        );
    }

    /**
     * Gather one ore more non-existing entity names.
     */
    private function gatherOneOrMoreNonExistingEntityNames(string $label): Collection
    {
        $names = collect();

        text(
            label: $label,
            required: "A {$this->entity->value} name is required.",
            hint: 'For more than one, separate names with commas.',
            validate: function (string $value) use (&$names) {
                $names = collect(explode(',', $value))->map(fn ($name) => trim($name))->filter()->unique()->values();

                if ($names->isEmpty()) {
                    return "A {$this->entity->value} name is required";
                }
                if ($names->count() > 100) {
                    return "Must not exceed more than 100 {$this->entity->value}s at a time";
                }

                foreach ($names as $name) {
                    if (strlen($name) > 100) {
                        return 'Names must not exceed 255 characters';
                    }
                    if (DB::table($this->entityTable)->where('name', $name)->whereNull('deleted_at')->exists()) {
                        return ucfirst($this->entity->value)." $name already exists";
                    }
                }

                return null;
            }
        );

        return $names;
    }

    /**
     * Resolve the actor for the action.
     */
    private function resolveActor(): void
    {
        if ($this->auditFeatureEnabled()) {
            Gatekeeper::setActor($this->gatherActor());
        }
    }

    /**
     * Gather the actor for the action.
     */
    private function gatherActor(): Model
    {
        $specifyActor = confirm(
            label: 'Do you want to specify an actor for this action?',
            default: true,
            no: 'Attribute this action to the system actor.',
        );

        if (! $specifyActor) {
            info('No actor specified. This action will be attributed to the system.');

            return new SystemActor;
        }

        $configuredModelLabels = $this->modelMetadataService->getConfiguredModelLabels();

        if ($configuredModelLabels->isEmpty()) {
            throw new GatekeeperConsoleException('No models are specified in the Gatekeeper configuration.');
        }

        $actorLabel = search(
            label: 'Search for the model of the actor',
            options: fn (string $value) => strlen($value) > 0
                ? $configuredModelLabels->filter(fn (string $label) => stripos($label, trim($value)) !== false)->all()
                : $configuredModelLabels->all(),
            required: 'An actor model label is required.',
            scroll: 10,
        );

        $actor = $this->resolveModel($actorLabel);

        [$actorClass, $actorPrimaryKey] = [$actor::class, $actor->getKey()];
        info("Actor [$actorClass] with primary key [$actorPrimaryKey] will be attributed to this action.");

        return $actor;
    }

    /**
     * Gather the actee for the action.
     */
    private function gatherActee(): Model
    {
        $configuredModelLabels = $this->modelMetadataService->getConfiguredModelLabels();

        if ($configuredModelLabels->isEmpty()) {
            throw new GatekeeperConsoleException('No models are specified in the Gatekeeper configuration.');
        }

        $acteeLabel = search(
            label: 'Search for the model being acted upon',
            options: fn (string $value) => strlen($value) > 0
                ? $configuredModelLabels->filter(fn (string $label) => stripos($label, trim($value)) !== false)->all()
                : $configuredModelLabels->all(),
            required: 'An actee model label is required.',
            scroll: 10,
        );

        $actee = $this->resolveModel($acteeLabel);

        [$acteeClass, $acteePrimaryKey] = [$actee::class, $actee->getKey()];
        info("Actee [$acteeClass] with primary key [$acteePrimaryKey] will be acted upon.");

        return $actee;
    }

    /**
     * Resolve a model instance based on the label and primary key.
     */
    private function resolveModel(string $label): Model
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($label);
        $searchable = collect($modelData->searchable);
        $instance = new $modelData->class;

        if (empty($searchable)) {
            throw new GatekeeperConsoleException("No columns are searchable for [$label] models");
        }

        $searchableList = $searchable->pluck('label')->implode(', ');

        $primaryKey = search(
            label: "Search by {$searchableList}",
            options: fn (string $value) => $this->modelService->getModels(ModelPagePacket::from(['model_label' => $label, 'search_term' => trim($value)]))->mapWithKeys(function (array $model) {
                $result = [];

                foreach ($model['displayable'] as $displayableEntry) {
                    $result[] = $this->formatDisplayField($displayableEntry['label'], $model['display'][$displayableEntry['column']], $displayableEntry['cli_width']);
                }

                if (empty($result)) {
                    $result[] = $this->formatDisplayField($model['model_label'], $model['model_pk'], $displayableEntry['cli_width']);
                }

                return [(string) $model['model_pk'] => implode(' | ', $result)];
            })->all(),
            required: 'A model is required.',
            validate: [Rule::exists($instance->getTable(), $instance->getKeyName())],
        );

        return $modelData->class::where($instance->getKeyName(), $primaryKey)->firstOrFail();
    }

    /**
     * Fix strings to let columns line up across rows.
     */
    private function formatDisplayField(string $label, string $value, ?int $width): string
    {
        $line = "{$label}: {$value}";
        $width = $width ?: 25;

        if (strlen($line) > $width) {
            $line = substr($line, 0, $width - 3).'...';
        }

        return str_pad($line, $width);
    }

    /**
     * Get the options for the actions available in this command.
     */
    private function getActionOptions(): array
    {
        return [
            AuditLogAction::build($this->entity, AuditLogActionVerb::Create)->value => "Create one or more new {$this->entity->value}s",
            AuditLogAction::build($this->entity, AuditLogActionVerb::UpdateName)->value => "Update an existing {$this->entity->value}'s name",
            AuditLogAction::build($this->entity, AuditLogActionVerb::GrantByDefault)->value => "Grant one or more {$this->entity->value}s by default",
            AuditLogAction::build($this->entity, AuditLogActionVerb::RevokeDefaultGrant)->value => "Revoke the default grant of one or more {$this->entity->value}s",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Deactivate)->value => "Deactivate one or more active {$this->entity->value}s",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Reactivate)->value => "Reactivate one or more inactive {$this->entity->value}s",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Delete)->value => "Delete one or more {$this->entity->value}s",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Assign)->value => "Assign one or more {$this->entity->value}s to a model",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Unassign)->value => "Unassign one or more {$this->entity->value}s from a model",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Deny)->value => "Deny one or more {$this->entity->value}s from a model",
            AuditLogAction::build($this->entity, AuditLogActionVerb::Undeny)->value => "Undeny one or more {$this->entity->value}s from a model",
        ];
    }

    /**
     * Filter entity names based on the action and search term.
     */
    private function filterEntityNamesForAction(string $value): array
    {
        $all = $this->entityService->getAll();

        $filtered = match ($this->action) {
            AuditLogAction::build($this->entity, AuditLogActionVerb::UpdateName)->value,
            AuditLogAction::build($this->entity, AuditLogActionVerb::Delete)->value,
            AuditLogAction::build($this->entity, AuditLogActionVerb::Assign)->value,
            AuditLogAction::build($this->entity, AuditLogActionVerb::Unassign)->value, => $all,

            AuditLogAction::build($this->entity, AuditLogActionVerb::RevokeDefaultGrant)->value, => $all->filter(fn (AbstractBaseEntityPacket $packet) => $packet->grantedByDefault),

            AuditLogAction::build($this->entity, AuditLogActionVerb::GrantByDefault)->value, => $all->filter(fn (AbstractBaseEntityPacket $packet) => ! $packet->grantedByDefault),

            AuditLogAction::build($this->entity, AuditLogActionVerb::Deactivate)->value, => $all->filter(fn (AbstractBaseEntityPacket $packet) => $packet->isActive),

            AuditLogAction::build($this->entity, AuditLogActionVerb::Reactivate)->value, => $all->filter(fn (AbstractBaseEntityPacket $packet) => ! $packet->isActive),

            default => $all,
        };

        if ($filtered->isEmpty()) {
            throw new GatekeeperConsoleException(match ($this->action) {
                AuditLogAction::build($this->entity, AuditLogActionVerb::UpdateName)->value,
                AuditLogAction::build($this->entity, AuditLogActionVerb::Delete)->value,
                AuditLogAction::build($this->entity, AuditLogActionVerb::Assign)->value,
                AuditLogAction::build($this->entity, AuditLogActionVerb::Unassign)->value, => "No {$this->entity->value}s found.",

                AuditLogAction::build($this->entity, AuditLogActionVerb::RevokeDefaultGrant)->value, => "No {$this->entity->value}s granted by default found.",

                AuditLogAction::build($this->entity, AuditLogActionVerb::GrantByDefault)->value, => "No {$this->entity->value}s not granted by default found.",

                AuditLogAction::build($this->entity, AuditLogActionVerb::Deactivate)->value, => "No active {$this->entity->value}s found.",

                AuditLogAction::build($this->entity, AuditLogActionVerb::Reactivate)->value, => "No inactive {$this->entity->value}s found.",

                default => "No {$this->entity->value}s found.",
            });
        }

        return $filtered
            ->filter(fn (AbstractBaseEntityPacket $packet) => stripos($packet->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
