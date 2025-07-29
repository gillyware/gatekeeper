<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\EntityRepositoryInterface;
use Gillyware\Gatekeeper\Exceptions\Feature\FeatureNotFoundException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * @implements EntityRepositoryInterface<Feature>
 */
class FeatureRepository implements EntityRepositoryInterface
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
        return Schema::hasTable((new Feature)->getTable());
    }

    /**
     * Check if a feature with the given name exists.
     */
    public function exists(string $featureName): bool
    {
        return Feature::query()->where('name', $featureName)->exists();
    }

    /**
     * Get all features.
     *
     * @return Collection<string, Feature>
     */
    public function all(): Collection
    {
        $features = $this->cacheService->getAllFeatures();

        if ($features) {
            return $features;
        }

        $features = Feature::all()->mapWithKeys(fn (Feature $feature) => [$feature->name => $feature]);

        $this->cacheService->putAllFeatures($features);

        return $features;
    }

    /**
     * Find a feature by its name.
     */
    public function findByName(string $featureName): ?Feature
    {
        return $this->all()->get($featureName);
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
     * Update an existing feature name.
     *
     * @param  Feature  $feature
     */
    public function updateName($feature, string $newFeatureName): Feature
    {
        if ($feature->update(['name' => $newFeatureName])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Grant a feature to all models that are not explicitly denying it.
     *
     * @param  Feature  $feature
     */
    public function grantByDefault($feature): Feature
    {
        if ($feature->update(['grant_by_default' => true])) {
            $this->cacheService->clear();
        }

        return $feature;
    }

    /**
     * Revoke a feature's default grant.
     *
     * @param  Feature  $feature
     */
    public function revokeDefaultGrant($feature): Feature
    {
        if ($feature->update(['grant_by_default' => false])) {
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
     * Get all features a specific model is on.
     *
     * @return Collection<string, Feature>
     */
    public function assignedToModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => ! $link['denied'])
            ->map(fn (array $link) => $link['feature']);
    }

    /**
     * Get all features denied from a specific model.
     *
     * @return Collection<string, Feature>
     */
    public function deniedFromModel(Model $model): Collection
    {
        return $this->forModel($model)
            ->filter(fn (array $link) => $link['denied'])
            ->map(fn (array $link) => $link['feature']);
    }

    /**
     * Get a page of features.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        $query = Feature::query()->whereLike('name', "%{$packet->searchTerm}%");

        $query = match ($packet->prioritizedAttribute) {
            'name' => $query
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            'grant_by_default' => $query
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('is_active', $packet->isActiveOrder),
            'is_active' => $query
                ->orderBy('is_active', $packet->isActiveOrder)
                ->orderBy('name', $packet->nameOrder)
                ->orderBy('grant_by_default', $packet->grantByDefaultOrder),
            default => $query,
        };

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Get all features for a specific model.
     *
     * @return Collection<string, array{feature: Feature, denied: bool}>
     */
    private function forModel(Model $model): Collection
    {
        return $this->linksForModel($model)
            ->mapWithKeys(function (array $link) {
                [$name, $denied] = [$link['name'], $link['denied']];

                return [
                    $name => [
                        'feature' => $this->findByName($name),
                        'denied' => $denied,
                    ],
                ];
            });
    }

    /**
     * Get all features for a specific model.
     *
     * @return Collection<int, array{name: string, denied: bool}>
     */
    private function linksForModel(Model $model): Collection
    {
        $allFeatureLinks = $this->cacheService->getModelFeatureLinks($model);

        if ($allFeatureLinks) {
            return $allFeatureLinks;
        }

        if (! $this->modelInteractsWithFeatures($model)) {
            return collect();
        }

        $allFeatureLinks = $model->features()
            ->select([
                'name' => (new Feature)->qualifyColumn('name'),
                'denied' => (new ModelHasFeature)->qualifyColumn('denied'),
            ])
            ->whereNull((new ModelHasFeature)->qualifyColumn('deleted_at'))
            ->get(['name', 'denied'])
            ->map(fn (Feature $feature) => $feature->only(['name', 'denied']));

        $this->cacheService->putModelFeatureLinks($model, $allFeatureLinks);

        return $allFeatureLinks;
    }
}
