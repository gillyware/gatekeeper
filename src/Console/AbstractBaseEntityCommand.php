<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperConsoleException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Packets\Models\ModelPagePacket;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\confirm;
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

    public function __construct(
        private readonly ModelService $modelService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {
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
     * Gather one existing entity name.
     */
    protected function gatherOneExistingEntityName(): string
    {
        $actionVerb = str($this->action)->after('_')->toString();

        return search(
            label: "Search for the {$this->entity->value} to $actionVerb",
            options: fn (string $value) => $this->filterEntityNamesForAction($value),
            required: "A {$this->entity->value} name is required.",
            validate: ['string', 'max:255', Rule::exists($this->entityTable, 'name')],
            scroll: 10,
        );
    }

    /**
     * Gather one or more existing entity names.
     */
    protected function gatherOneOrMoreExistingEntityNames(): Collection
    {
        $actionVerb = str($this->action)->after('_')->toString();

        return collect(multisearch(
            label: "Search for the {$this->entity->value}(s) to $actionVerb",
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
    protected function gatherOneNonExistingEntityName(string $label): string
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
    protected function gatherOneOrMoreNonExistingEntityNames(string $label): Collection
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

        $actor = $this->resolveModel($actorLabel);

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
    abstract protected function getActionOptions(): array;

    /**
     * Filter entity names based on the action and search term.
     */
    abstract protected function filterEntityNamesForAction(string $searchTerm): array;
}
