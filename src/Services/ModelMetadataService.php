<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Exceptions\Model\ModelConfigurationException;
use Gillyware\Gatekeeper\Packets\Config\ManageableModelPacket;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ModelMetadataService
{
    use EnforcesForGatekeeper;

    /** @var Collection<ManageableModelPacket> */
    protected Collection $models;

    /**
     * Get all configured manageable models.
     */
    public function getConfiguredModels(): Collection
    {
        if (isset($this->models)) {
            return $this->models;
        }

        $manageableModels = collect(Config::get('gatekeeper.models.manageable', []))
            ->map(fn (array $manageableModel) => ManageableModelPacket::from($manageableModel));

        return $this->models = $manageableModels;
    }

    /**
     * Get just the labels of all configured models.
     */
    public function getConfiguredModelLabels(): Collection
    {
        return $this->getConfiguredModels()->pluck('label');
    }

    /**
     * Get all configured manageable models with metadata.
     */
    public function getConfiguredModelsWithMetadata(): Collection
    {
        return $this->getConfiguredModels()->map(fn (ManageableModelPacket $packet) => $packet->toArray())->values();
    }

    /**
     * Get the full config entry for a model by label.
     */
    public function getModelDataByLabel(string $label): ManageableModelPacket
    {
        $data = $this->getConfiguredModels()->first(fn (ManageableModelPacket $modelData) => $modelData->label === $label);

        if (! $data) {
            throw new ModelConfigurationException("Model with label '{$label}' not found in configuration.");
        }

        return $data;
    }
}
