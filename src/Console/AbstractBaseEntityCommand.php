<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Exceptions\GatekeeperConsoleException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

abstract class AbstractBaseEntityCommand extends AbstractBaseGatekeeperCommand
{
    use EnforcesForGatekeeper;

    protected string $entity;

    protected string $entityTable;

    protected string $action;

    public function __construct(private readonly ModelMetadataService $modelMetadataService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->clearTerminal();

        $this->action = $this->gatherEntityAction();

        return self::SUCCESS;
    }

    /**
     * Gather the action to perform on an entity.
     */
    protected function gatherEntityAction(): string
    {
        return select(
            label: 'What action do you want to perform?',
            options: $this->getActionOptions(),
            required: 'An action is required.',
            validate: ['string', Rule::in(array_keys($this->getActionOptions()))],
            default: array_key_first($this->getActionOptions()),
            scroll: 10,
        );
    }

    /**
     * Gather the name of an entity for a specific action.
     */
    protected function gatherEntityName(): string
    {
        $actionVerb = str($this->action)->after('_')->toString();

        if (str_contains($this->action, 'create')) {
            return text(
                label: "What is the name of the {$this->entity} you want to create?",
                required: "A {$this->entity} name is required.",
                validate: ['string', 'max:255', Rule::unique($this->entityTable, 'name')->withoutTrashed()],
            );
        }

        return search(
            label: "Search for the {$this->entity} to $actionVerb",
            options: fn (string $value) => $this->filterEntityNamesForAction($value),
            required: "A {$this->entity} name is required.",
            validate: ['string', 'max:255', Rule::exists($this->entityTable, 'name')],
            scroll: 10,
        );
    }

    /**
     * Gather multiple entity names for a specific action.
     */
    protected function gatherMultipleEntityNames(): Collection
    {
        $actionVerb = str($this->action)->after('_')->toString();

        return collect(multisearch(
            label: "Search for the {$this->entity}s to $actionVerb",
            options: fn (string $value) => $this->filterEntityNamesForAction($value),
            required: "At least one {$this->entity} name is required.",
            validate: ['array', 'min:1', Rule::exists($this->entityTable, 'name')],
            scroll: 10,
            hint: 'Select options with the space bar and confirm with enter.',
        ));
    }

    /**
     * Resolve the actor for the action.
     */
    protected function resolveActor(): void
    {
        if ($this->auditFeatureEnabled()) {
            Gatekeeper::setActor($this->gatherActor());
        }
    }

    /**
     * Gather the actor for the action.
     */
    protected function gatherActor(): Model
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

        $actor = $this->resolveModel($actorLabel, 'What is the primary key of the actor?');

        [$actorClass, $actorPrimaryKey] = [$actor::class, $actor->getKey()];
        info("Actor [$actorClass] with primary key [$actorPrimaryKey] will be attributed to this action.");

        return $actor;
    }

    /**
     * Gather the actee for the action.
     */
    protected function gatherActee(): Model
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

        $actee = $this->resolveModel($acteeLabel, 'What is the primary key of the model being acted upon?');

        [$acteeClass, $acteePrimaryKey] = [$actee::class, $actee->getKey()];
        info("Actee [$acteeClass] with primary key [$acteePrimaryKey] will be acted upon.");

        return $actee;
    }

    /**
     * Resolve a model instance based on the label and primary key.
     */
    private function resolveModel(string $label, string $prompt): Model
    {
        $class = $this->modelMetadataService->getClassFromLabel($label);

        if (! $class) {
            throw new GatekeeperConsoleException("Model class for label [$label] does not exist.");
        }

        $instance = new $class;

        $primaryKey = text(
            label: $prompt,
            required: 'A primary key is required.',
            validate: ['string', 'regex:/^[\w-]+$/', Rule::exists($instance->getTable(), $instance->getKeyName())],
        );

        return $class::where($instance->getKeyName(), $primaryKey)->firstOrFail();
    }

    /**
     * Get the options for the actions available in this command.
     */
    abstract protected function getActionOptions(): array;

    /**
     * Filter entity names based on the action and search term.
     */
    abstract protected function filterEntityNamesForAction(string $searchTerm): array;
}
