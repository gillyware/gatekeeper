<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\FeatureRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Feature\FeatureNotFoundException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * @implements FeatureRepositoryInterface<Feature>
 */
class FeatureRepository implements FeatureRepositoryInterface
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    /**
     * Check if the features table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES));
    }

    /**
     * Check if a feature with the given name exists.
     */
    public function exists(string $featureName): bool
    {
        return Feature::query()->where('name', $featureName)->exists();
    }

    /**
     * Find a feature by its name.
     */
    public function findByName(string $featureName): ?Feature
    {
        return $this->all()->get($featureName);
    }

    /**
     * Find a feature by its name for a specific model.
     */
    public function findByNameForModel(Model $model, string $featureName): ?Feature
    {
        return $this->forModel($model)->get($featureName);
    }

    /**
     * Find a feature by its name, or fail.
     */
    public function findOrFailByName(string $featureName): Feature
    {
        $feature = $this->findByName($featureName);

        if (! $feature) {
            throw new FeatureNotFoundException($featureName);
        }

        return $feature;
    }

    /**
     * Create a new feature.
     */
    public function create(string $featureName): Feature
    {
        $feature = new Feature(['name' => $featureName]);

        if ($feature->save()) {
            $this->cacheService->invalidateCacheForAllFeatures();
        }

        return $feature->fresh();
    }

    /**
     * Update an existing feature.
     *
     * @param  Feature  $feature
     */
    public function update($feature, string $newFeatureName): Feature
    {
        if ($feature->update(['name' => $newFeatureName])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Set a feature as off by default.
     *
     * @param  Feature  $feature
     */
    public function turnOffByDefault($feature): Feature
    {
        if ($feature->update(['default_enabled' => false])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Set a feature as on by default.
     *
     * @param  Feature  $feature
     */
    public function turnOnByDefault($feature): Feature
    {
        if ($feature->update(['default_enabled' => true])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Deactivate a feature.
     *
     * @param  Feature  $feature
     */
    public function deactivate($feature): Feature
    {
        if ($feature->update(['is_active' => false])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Reactivate a feature.
     *
     * @param  Feature  $feature
     */
    public function reactivate($feature): Feature
    {
        if ($feature->update(['is_active' => true])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Delete a feature.
     *
     * @param  Feature  $feature
     */
    public function delete($feature): bool
    {
        // Unassign all permissions from the feature (without audit logging).
        $this->modelHasPermissionRepository->deleteForModel($feature);

        $deleted = $feature->delete();

        if ($deleted) {
            $this->cacheService->clear();
        }

        return $deleted;
    }

    /**
     * Get all features.
     *
     * @return Collection<Feature>
     */
    public function all(): Collection
    {
        $features = $this->cacheService->getAllFeatures();

        if ($features) {
            return $features;
        }

        $features = Feature::all()->mapWithKeys(fn (Feature $r) => [$r->name => $r]);

        $this->cacheService->putAllFeatures($features);

        return $features;
    }

    /**
     * Get all active features.
     *
     * @return Collection<Feature>
     */
    public function active(): Collection
    {
        return $this->all()->filter(fn (Feature $feature) => $feature->is_active);
    }

    /**
     * Get all features where the name is in the provided array or collection.
     *
     * @return Collection<Feature>
     */
    public function whereNameIn(array|Collection $featureNames): Collection
    {
        return $this->all()->whereIn('name', $featureNames);
    }

    /**
     * Get all feature names for a specific model.
     *
     * @return Collection<string>
     */
    public function namesForModel(Model $model): Collection
    {
        $allFeatureNames = $this->cacheService->getModelFeatureNames($model);

        if ($allFeatureNames) {
            return $allFeatureNames;
        }

        if (! $this->modelInteractsWithFeatures($model)) {
            return collect();
        }

        $featuresTable = Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES);
        $modelHasFeaturesTable = Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES);

        $allFeatureNames = $model->features()
            ->select("$featuresTable.*")
            ->whereNull("$modelHasFeaturesTable.deleted_at")
            ->pluck("$featuresTable.name")
            ->values();

        $this->cacheService->putModelFeatureNames($model, $allFeatureNames);

        return $allFeatureNames;
    }

    /**
     * Get all features for a specific model.
     *
     * @return Collection<Feature>
     */
    public function forModel(Model $model): Collection
    {
        $namesForModel = $this->namesForModel($model);

        return $this->whereNameIn($namesForModel);
    }

    /**
     * Get all active features for a specific model.
     *
     * @return Collection<Feature>
     */
    public function activeForModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (Feature $feature) => $feature->is_active);
    }

    /**
     * Get a page of features.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Feature::query()->whereLike('name', "%{$packet->searchTerm}%");

        if ($packet->prioritizedAttribute === 'is_active') {
            $query = $query
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('name', $packet->nameOrder);
        } else {
            $query = $query
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder);
        }

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }
}
