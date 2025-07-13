<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Exceptions\Model\ModelConfigurationException;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use ReflectionClass;

class ModelMetadataService
{
    use EnforcesForGatekeeper;

    protected Collection $models;

    /**
     * Get all configured manageable models.
     */
    public function getConfiguredModels(): Collection
    {
        if (isset($this->models)) {
            return $this->models;
        }

        $this->models = collect(Config::get('gatekeeper.models.manageable', []));

        return $this->models;
    }

    /**
     * Get just the labels of all configured models.
     */
    public function getConfiguredModelLabels(): Collection
    {
        return $this->getConfiguredModels()->pluck('label');
    }

    /**
     * Get the full config entry for a model by label.
     */
    public function getModelDataByLabel(string $label): array
    {
        $data = $this->getConfiguredModels()->first(fn ($model) => $model['label'] === $label);

        if (! $data) {
            throw new ModelConfigurationException("Model with label '{$label}' not found in configuration.");
        }

        return $data;
    }

    /**
     * Get the class name from a label.
     */
    public function getClassFromLabel(string $label): string
    {
        $modelData = $this->getModelDataByLabel($label);

        $modelClass = $this->getClassFromModelData($modelData);

        if (! $modelClass) {
            throw new ModelConfigurationException("Model with label '{$label}' not found or not manageable.");
        }

        return $modelClass;
    }

    /**
     * Get the fully qualified class name from a model config.
     */
    public function getClassFromModelData(array $modelData): ?string
    {
        $className = data_get($modelData, 'class');

        if (! class_exists($className)) {
            return null;
        }

        if (! is_subclass_of($className, Model::class)) {
            return null;
        }

        if ((new ReflectionClass($className))->isAbstract()) {
            return null;
        }

        return $className;
    }
}
