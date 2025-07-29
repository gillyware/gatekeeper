<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @implements ModelHasEntityRepositoryInterface<Feature, ModelHasFeature>
 */
class ModelHasFeatureRepository implements ModelHasEntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a feature is assigned to any model.
     *
     * @param  Feature  $feature
     */
    public function existsForEntity($feature): bool
    {
        return ModelHasFeature::query()->where('feature_id', $feature->id)->exists();
    }

    /**
     * Assign a feature to a model.
     *
     * @param  Feature  $feature
     */
    public function assignToModel(Model $model, $feature): ModelHasFeature
    {
        $modelHasFeature = ModelHasFeature::query()->updateOrCreate([
            'feature_id' => $feature->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => false,
        ]);

        $this->cacheService->invalidateCacheForModelFeatureLinks($model);

        return $modelHasFeature;
    }

    /**
     * Delete all non-denied feature assignments for a given model and feature.
     *
     * @param  Feature  $feature
     */
    public function unassignFromModel(Model $model, $feature): bool
    {
        ModelHasFeature::forModel($model)
            ->where('feature_id', $feature->id)
            ->where('denied', false)
            ->delete();

        $this->cacheService->invalidateCacheForModelFeatureLinks($model);

        return true;
    }

    /**
     * Deny a feature from a model.
     *
     * @param  Feature  $feature
     */
    public function denyFromModel(Model $model, $feature): ModelHasFeature
    {
        $modelHasFeature = ModelHasFeature::query()->updateOrCreate([
            'feature_id' => $feature->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => true,
        ]);

        $this->cacheService->invalidateCacheForModelFeatureLinks($model);

        return $modelHasFeature;
    }

    /**
     * Delete all denied feature assignments for a given model and feature.
     *
     * @param  Feature  $feature
     */
    public function undenyFromModel(Model $model, $feature): bool
    {
        ModelHasFeature::forModel($model)
            ->where('feature_id', $feature->id)
            ->where('denied', true)
            ->delete();

        $this->cacheService->invalidateCacheForModelFeatureLinks($model);

        return true;
    }

    /**
     * Delete all feature assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasFeature::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelFeatureLinks($model);

        return true;
    }

    /**
     * Delete all assignments for a given feature.
     *
     * @param  Feature  $feature
     */
    public function deleteForEntity($feature): bool
    {
        ModelHasFeature::query()->where('feature_id', $feature->id)
            ->with('model')
            ->get()
            ->each(function (ModelHasFeature $modelHasFeature) {
                $modelHasFeature->delete();

                if ($modelHasFeature->model) {
                    $this->cacheService->invalidateCacheForModelFeatureLinks($modelHasFeature->model);
                }
            });

        return true;
    }

    /**
     * Search model feature assignments by feature name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasFeature::query()
            ->select((new ModelHasFeature)->qualifyColumn('*'))
            ->join((new Feature)->getTable(), (new Feature)->qualifyColumn('id'), '=', (new ModelHasFeature)->qualifyColumn('feature_id'))
            ->forModel($model)
            ->where('denied', false)
            ->whereIn('feature_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Feature)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('feature:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned features by feature name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return Feature::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model) {
                $subquery->select('feature_id')
                    ->from((new ModelHasFeature)->getTable())
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull((new ModelHasFeature)->qualifyColumn('deleted_at'));
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search denied features by feature name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasFeature::query()
            ->select((new ModelHasFeature)->qualifyColumn('*'))
            ->join((new Feature)->getTable(), (new Feature)->qualifyColumn('id'), '=', (new ModelHasFeature)->qualifyColumn('feature_id'))
            ->forModel($model)
            ->where('denied', true)
            ->whereIn('feature_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Feature)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('feature:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }
}
