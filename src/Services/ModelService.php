<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Packets\Config\ManageableModelPacket;
use Gillyware\Gatekeeper\Packets\Models\ModelPagePacket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelService
{
    public function __construct(private readonly ModelMetadataService $modelMetadataService) {}

    /**
     * Resolve a model instance by its primary key.
     */
    public function find(ManageableModelPacket $modelData, int|string $pk): Model
    {
        $model = $modelData->class::find($pk);

        if (! $model) {
            throw new GatekeeperException("Model with primary key '{$pk}' not found in class '{$modelData->class}'.");
        }

        return $model;
    }

    /**
     * Search for models based on a label and search term.
     */
    public function getModels(ModelPagePacket $packet): Collection
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($packet->modelLabel);

        $query = $modelData->class::query();

        foreach ($modelData->searchable as $searchableEntry) {
            $query->orWhereLike($searchableEntry['column'], "%{$packet->searchTerm}%");
        }

        return $query->limit(10)->get()->map(fn (Model $model) => array_merge($modelData->toArray(), [
            'model_pk' => (string) $model->getKey(),
            'display' => $this->prepareModelForDisplay($modelData, $model),
        ]));
    }

    /**
     * Prepare a model for display in the UI.
     */
    public function prepareModelForDisplay(ManageableModelPacket $modelData, Model $model): array
    {
        $result = [];

        foreach ($modelData->displayable as $displayableEntry) {
            $result[$displayableEntry['column']] = $model->{$displayableEntry['column']};
        }

        return $result;
    }
}
