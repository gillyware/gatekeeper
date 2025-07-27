<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperConsoleException;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Illuminate\Support\Facades\Config;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class FeatureCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:feature';

    protected $description = 'Manage features';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
        private readonly FeatureRepository $featureRepository,
    ) {
        parent::__construct($modelService, $modelMetadataService);

        $this->entity = GatekeeperEntity::Feature;
        $this->entityTable = Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES);
    }

    public function handle(): int
    {
        parent::handle();

        try {
            match ($this->action) {
                AuditLogAction::CreateFeature->value => $this->handleCreate(),
                AuditLogAction::UpdateFeature->value => $this->handleUpdate(),
                AuditLogAction::TurnFeatureOffByDefault->value => $this->handleTurnOffByDefault(),
                AuditLogAction::TurnFeatureOnByDefault->value => $this->handleTurnOnByDefault(),
                AuditLogAction::DeactivateFeature->value => $this->handleDeactivate(),
                AuditLogAction::ReactivateFeature->value => $this->handleReactivate(),
                AuditLogAction::DeleteFeature->value => $this->handleDelete(),
                AuditLogAction::AssignFeature->value => $this->handleAssign(),
                AuditLogAction::RevokeFeature->value => $this->handleRevoke(),
            };

            return self::SUCCESS;
        } catch (GatekeeperException $e) {
            error($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            $actionVerb = str($this->action)->after('_')->toString();
            error("An unexpected error occurred while trying to {$actionVerb} the feature: {$e->getMessage()}");
        }

        return self::FAILURE;
    }

    /**
     * Handle the creation of one or more new features.
     */
    private function handleCreate(): void
    {
        $featureNames = $this->gatherOneOrMoreNonExistingEntityNames("What is the name of the {$this->entity->value} you want to create?");

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::createFeature($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' created successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to create feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the update of an existing feature.
     */
    private function handleUpdate(): void
    {
        $featureName = $this->gatherOneExistingEntityName();

        $newFeatureName = $this->gatherOneNonExistingEntityName("What will be the new {$this->entity->value} name?");

        $this->resolveActor();

        Gatekeeper::updateFeature($featureName, $newFeatureName);

        info("Feature '$featureName' updated successfully to '$newFeatureName'.");
    }

    /**
     * Handle the turning off by defaule one or more enabled by default features.
     */
    private function handleTurnOffByDefault(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::turnFeatureOffByDefault($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' successfully turned off by default.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to turn feature{$plural} '{$failures->implode(', ')}' off by default.");
        }
    }

    /**
     * Handle the turning on by defaule one or more disabled by default features.
     */
    private function handleTurnOnByDefault(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::turnFeatureOnByDefault($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' successfully turned on by default.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to turn feature{$plural} '{$failures->implode(', ')}' on by default.");
        }
    }

    /**
     * Handle the deactivation of one or more active features.
     */
    private function handleDeactivate(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::deactivateFeature($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' deactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to deactivate feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the reactivation of one or more deactivated features.
     */
    private function handleReactivate(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::reactivateFeature($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' reactivated successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to reactivate feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the deletion of one or more existing features.
     */
    private function handleDelete(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) {
            try {
                Gatekeeper::deleteFeature($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' deleted successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to delete feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the assignment of one or more features to a model.
     */
    private function handleAssign(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) use ($actee) {
            try {
                Gatekeeper::for($actee)->turnFeatureOn($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' turned on successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to turn on feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * Handle the revocation of one or more features from a model.
     */
    private function handleRevoke(): void
    {
        $featureNames = $this->gatherOneOrMoreExistingEntityNames();

        $actee = $this->gatherActee();

        $this->resolveActor();

        [$successes, $failures] = $featureNames->partition(function (string $featureName) use ($actee) {
            try {
                Gatekeeper::for($actee)->turnFeatureOff($featureName);

                return true;
            } catch (GatekeeperException $e) {
                error($e->getMessage());

                return false;
            }
        });

        if ($successes->isNotEmpty()) {
            $plural = $successes->count() > 1 ? 's' : '';
            info("Feature{$plural} '{$successes->implode(', ')}' turned off successfully.");
        }

        if ($failures->isNotEmpty()) {
            $plural = $failures->count() > 1 ? 's' : '';
            error("Failed to turn off feature{$plural} '{$failures->implode(', ')}'.");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getActionOptions(): array
    {
        return [
            AuditLogAction::CreateFeature->value => 'Create one or more new features',
            AuditLogAction::UpdateFeature->value => 'Update an existing feature',
            AuditLogAction::TurnFeatureOffByDefault->value => 'Turn one or more features off by default',
            AuditLogAction::TurnFeatureOnByDefault->value => 'Turn one or more features on by default',
            AuditLogAction::DeactivateFeature->value => 'Deactivate one or more active features',
            AuditLogAction::ReactivateFeature->value => 'Reactivate one or more inactive features',
            AuditLogAction::DeleteFeature->value => 'Delete one or more existing features',
            AuditLogAction::AssignFeature->value => 'Turn one or more features on for a model',
            AuditLogAction::RevokeFeature->value => 'Turn one or more features off for a model',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filterEntityNamesForAction(string $value): array
    {
        $all = $this->featureRepository->all();

        $filtered = match ($this->action) {
            AuditLogAction::UpdateFeature->value, AuditLogAction::DeleteFeature->value, AuditLogAction::AssignFeature->value, AuditLogAction::RevokeFeature->value => $all,
            AuditLogAction::TurnFeatureOffByDefault->value => $all->filter(fn (Feature $feature) => $feature->default_enabled),
            AuditLogAction::TurnFeatureOnByDefault->value => $all->filter(fn (Feature $feature) => ! $feature->default_enabled),
            AuditLogAction::DeactivateFeature->value => $all->filter(fn (Feature $feature) => $feature->is_active),
            AuditLogAction::ReactivateFeature->value => $all->filter(fn (Feature $feature) => ! $feature->is_active),
            default => $all,
        };

        if ($filtered->isEmpty()) {
            throw new GatekeeperConsoleException(
                match ($this->action) {
                    AuditLogAction::UpdateFeature->value, AuditLogAction::DeleteFeature->value, AuditLogAction::AssignFeature->value, AuditLogAction::RevokeFeature->value => 'No features found.',
                    AuditLogAction::TurnFeatureOffByDefault->value => 'No features enabled by default found.',
                    AuditLogAction::TurnFeatureOnByDefault->value => 'No features disabled by default found.',
                    AuditLogAction::DeactivateFeature->value => 'No active features found.',
                    AuditLogAction::ReactivateFeature->value => 'No inactive features found.',
                    default => 'No features found.',
                });
        }

        return $filtered
            ->filter(fn (Feature $feature) => stripos($feature->name, trim($value)) !== false)
            ->pluck('name')
            ->toArray();
    }
}
