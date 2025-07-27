<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\FeatureServiceInterface;
use Gillyware\Gatekeeper\Enums\FeatureSourceType;
use Gillyware\Gatekeeper\Exceptions\Feature\FeatureAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\AssignFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\CreateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\DeactivateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\DeleteFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\ReactivateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\RevokeFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\TurnedOffByDefaultFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\TurnedOnByDefaultFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\UpdateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasFeatureRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Feature, FeaturePacket>
 *
 * @implements FeatureServiceInterface<Feature, FeaturePacket>
 */
class FeatureService extends AbstractBaseEntityService implements FeatureServiceInterface
{
    public function __construct(
        private readonly FeatureRepository $featureRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasFeatureRepository $modelHasFeatureRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if the features table exists.
     */
    public function tableExists(): bool
    {
        return $this->featureRepository->tableExists();
    }

    /**
     * Check if a feature with the given name exists.
     */
    public function exists(string|UnitEnum $featureName): bool
    {
        $featureName = $this->resolveEntityName($featureName);

        return $this->featureRepository->exists($featureName);
    }

    /**
     * Create a new feature.
     */
    public function create(string|UnitEnum $featureName): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $featureName = $this->resolveEntityName($featureName);

        if ($this->exists($featureName)) {
            throw new FeatureAlreadyExistsException($featureName);
        }

        $createdFeature = $this->featureRepository->create($featureName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(CreateFeatureAuditLogPacket::make($createdFeature));
        }

        return $createdFeature->toPacket();
    }

    /**
     * Update an existing feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function update($feature, string|UnitEnum $newFeatureName): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $newFeatureName = $this->resolveEntityName($newFeatureName);

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($this->exists($newFeatureName) && $currentFeature->name !== $newFeatureName) {
            throw new FeatureAlreadyExistsException($newFeatureName);
        }

        $oldFeatureName = $currentFeature->name;
        $updatedFeature = $this->featureRepository->update($currentFeature, $newFeatureName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdateFeatureAuditLogPacket::make($updatedFeature, $oldFeatureName));
        }

        return $updatedFeature->toPacket();
    }

    /**
     * Set a feature as off by default.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function turnOffByDefault($feature): FeaturePacket
    {
        $this->enforceAuditFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if (! $currentFeature->default_enabled) {
            return $currentFeature->toPacket();
        }

        $defaultedOffFeature = $this->featureRepository->turnOffByDefault($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(TurnedOffByDefaultFeatureAuditLogPacket::make($defaultedOffFeature));
        }

        return $defaultedOffFeature->toPacket();
    }

    /**
     * Set a feature as on by default.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function turnOnByDefault($feature): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($currentFeature->default_enabled) {
            return $currentFeature->toPacket();
        }

        $defaultedOnFeature = $this->featureRepository->turnOnByDefault($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(TurnedOnByDefaultFeatureAuditLogPacket::make($defaultedOnFeature));
        }

        return $defaultedOnFeature->toPacket();
    }

    /**
     * Deactivate a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function deactivate($feature): FeaturePacket
    {
        $this->enforceAuditFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if (! $currentFeature->is_active) {
            return $currentFeature->toPacket();
        }

        $deactivatedFeature = $this->featureRepository->deactivate($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeactivateFeatureAuditLogPacket::make($deactivatedFeature));
        }

        return $deactivatedFeature->toPacket();
    }

    /**
     * Reactivate a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function reactivate($feature): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($currentFeature->is_active) {
            return $currentFeature->toPacket();
        }

        $reactivatedFeature = $this->featureRepository->reactivate($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(ReactivateFeatureAuditLogPacket::make($reactivatedFeature));
        }

        return $reactivatedFeature->toPacket();
    }

    /**
     * Delete a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function delete($feature): bool
    {
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature);

        if (! $feature) {
            return true;
        }

        // Delete any existing assignments for the feature being deleted.
        if ($this->modelHasFeatureRepository->existsForEntity($feature)) {
            $this->modelHasFeatureRepository->deleteForEntity($feature);
        }

        $deleted = $this->featureRepository->delete($feature);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeleteFeatureAuditLogPacket::make($feature));
        }

        return (bool) $deleted;
    }

    /**
     * Turn a feature on for a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function assignToModel(Model $model, $feature): bool
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();
        $this->enforceFeatureInteraction($model);
        $this->enforceModelIsNotFeature($model, 'Features cannot be assigned to other features');
        $this->enforceModelIsNotPermission($model, 'Features cannot be assigned to permissions');

        $feature = $this->resolveEntity($feature, orFail: true);

        // If the model already has this feature directly assigned, return true.
        if ($this->modelHasDirectly($model, $feature)) {
            return true;
        }

        $this->modelHasFeatureRepository->create($model, $feature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(AssignFeatureAuditLogPacket::make($model, $feature));
        }

        return true;
    }

    /**
     * Turn multiple features on for a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function assignAllToModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $feature);
        });

        return $result;
    }

    /**
     * Turn a feature off for a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function revokeFromModel(Model $model, $feature): bool
    {
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature, orFail: true);

        $revoked = $this->modelHasFeatureRepository->deleteForModelAndEntity($model, $feature);

        if ($revoked && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokeFeatureAuditLogPacket::make($model, $feature));
        }

        return $revoked;
    }

    /**
     * Turn multiple features off for a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->revokeFromModel($model, $feature);
        });

        return $result;
    }

    /**
     * Check if a model has the given feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function modelHas(Model $model, $feature): bool
    {
        // To access the feature, the features feature must be enabled and the model must be using the features trait.
        if (! $this->featuresFeatureEnabled() || ! $this->modelInteractsWithFeatures($model)) {
            return false;
        }

        $feature = $this->resolveEntity($feature);

        // The feature cannot be accessed if it does not exist or is inactive.
        if (! $feature || ! $feature->is_active) {
            return false;
        }

        // If the feature is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $feature)) {
            return true;
        }

        // If teams are enabled and the model interacts with teams, check if the model has the feature through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithFeature = $this->teamRepository
                ->activeForModel($model)
                ->some(fn (Team $team) => $team->hasFeature($feature));

            if ($onTeamWithFeature) {
                return true;
            }
        }

        return $feature->default_enabled;
    }

    /**
     * Check if a model directly has the given feature (not granted through teams).
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function modelHasDirectly(Model $model, $feature): bool
    {
        $feature = $this->resolveEntity($feature);

        return $feature && $this->featureRepository->activeForModel($model)->some(fn (Feature $r) => $feature->name === $r->name);
    }

    /**
     * Check if a model has any of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAny(Model $model, array|Arrayable $features): bool
    {
        return $this->resolveEntities($features)->filter()->some(
            fn (Feature $feature) => $this->modelHas($model, $feature)
        );
    }

    /**
     * Check if a model has all of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAll(Model $model, array|Arrayable $features): bool
    {
        return $this->resolveEntities($features)->every(
            fn (?Feature $feature) => $feature && $this->modelHas($model, $feature)
        );
    }

    /**
     * Find a feature by its name.
     */
    public function findByName(string|UnitEnum $featureName): ?FeaturePacket
    {
        return $this->resolveEntity($featureName)?->toPacket();
    }

    /**
     * Get all features.
     *
     * @return Collection<FeaturePacket>
     */
    public function getAll(): Collection
    {
        return $this->featureRepository->all()
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<FeaturePacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->featureRepository->all()
            ->filter(fn (Feature $feature) => $this->modelHas($model, $feature))
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all features directly assigned to a model.
     *
     * @return Collection<FeaturePacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->featureRepository->forModel($model)
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all effective features for the given model with the feature source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->featuresFeatureEnabled()) {
            return $result;
        }

        $this->featureRepository->active()
            ->filter(fn (Feature $feature) => $feature->default_enabled)
            ->each(function (Feature $feature) use (&$sourcesMap) {
                $sourcesMap[$feature->name][] = [
                    'type' => FeatureSourceType::DEFAULT,
                ];
            });

        if ($this->modelInteractsWithFeatures($model)) {
            $this->featureRepository->activeForModel($model)
                ->each(function (Feature $feature) use (&$sourcesMap) {
                    $sourcesMap[$feature->name][] = ['type' => FeatureSourceType::DIRECT];
                });
        }

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $this->teamRepository->activeForModel($model)
                ->each(function (Team $team) use (&$sourcesMap) {
                    $this->featureRepository->activeForModel($team)
                        ->each(function (Feature $feature) use (&$sourcesMap, $team) {
                            $sourcesMap[$feature->name][] = [
                                'type' => FeatureSourceType::TEAM,
                                'team' => $team->name,
                            ];
                        });
                });
        }

        foreach ($sourcesMap as $featureName => $sources) {
            $result->push([
                'name' => $featureName,
                'sources' => $sources,
            ]);
        }

        return $result;
    }

    /**
     * Get a page of features.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        return $this->featureRepository->getPage($packet)
            ->through(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get the feature model from the feature or feature name.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    protected function resolveEntity($feature, bool $orFail = false): ?Feature
    {
        if ($feature instanceof Feature) {
            return $feature;
        }

        $featureName = $this->resolveEntityName($feature);

        return $orFail
            ? $this->featureRepository->findOrFailByName($featureName)
            : $this->featureRepository->findByName($featureName);
    }
}
