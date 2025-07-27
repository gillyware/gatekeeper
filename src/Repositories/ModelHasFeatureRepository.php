<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

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
     * Create a new model feature assigment.
     *
     * @param  Feature  $feature
     */
    public function create(Model $model, $feature): ModelHasFeature
    {
        $modelHasFeature = ModelHasFeature::create([
            'feature_id' => $feature->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);

        $this->cacheService->invalidateCacheForModelFeatureNames($model);

        return $modelHasFeature;
    }

    /**
     * Delete all feature assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasFeature::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelFeatureNames($model);

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
                    $this->cacheService->invalidateCacheForModelFeatureNames($modelHasFeature->model);
                }
            });

        return true;
    }

    /**
     * Delete all feature assignments for a given model and feature.
     *
     * @param  Feature  $feature
     */
    public function deleteForModelAndEntity(Model $model, $feature): bool
    {
        ModelHasFeature::forModel($model)->where('feature_id', $feature->id)->delete();

        $this->cacheService->invalidateCacheForModelFeatureNames($model);

        return true;
    }

    /**
     * Search model feature assignments by feature name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $featuresTable = Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES);
        $modelFeaturesTable = Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES);

        $query = ModelHasFeature::query()
            ->select("$modelFeaturesTable.*")
            ->join($featuresTable, "$featuresTable.id", '=', "$modelFeaturesTable.feature_id")
            ->forModel($model)
            ->whereIn('feature_id', function ($sub) use ($featuresTable, $packet) {
                $sub->select('id')
                    ->from($featuresTable)
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc("$featuresTable.is_active")
            ->orderBy("$featuresTable.name")
            ->with('feature:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned features by feature name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $modelFeaturesTable = Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES);

        $query = Feature::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelFeaturesTable) {
                $subquery->select('feature_id')
                    ->from($modelFeaturesTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelFeaturesTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }
}
