<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Feature;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasFeatures
{
    use InteractsWithFeatures, InteractsWithTeams;

    /**
     * Assign a feature to the model.
     */
    public function turnFeatureOn(Feature|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->turnFeatureOn($feature);
    }

    /**
     * Assign multiple features to the model.
     */
    public function turnAllFeaturesOn(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->turnAllFeaturesOn($features);
    }

    /**
     * Revoke a feature from the model.
     */
    public function turnFeatureOff(Feature|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->turnFeatureOff($feature);
    }

    /**
     * Revoke multiple features from the model.
     */
    public function turnAllFeaturesOff(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->turnAllFeaturesOff($features);
    }

    /**
     * Check if the model has a given feature.
     */
    public function hasFeature(Feature|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->hasFeature($feature);
    }

    /**
     * Check if the model has any of the given features.
     */
    public function hasAnyFeature(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->hasAnyFeature($features);
    }

    /**
     * Check if the model has all of the given features.
     */
    public function hasAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->hasAllFeatures($features);
    }

    /**
     * Get all features assigned directly to a model.
     *
     * @return Collection<Feature>
     */
    public function getDirectFeatures(): Collection
    {
        return Gatekeeper::for($this)->getDirectFeatures();
    }

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<Feature>
     */
    public function getEffectiveFeatures(): Collection
    {
        return Gatekeeper::for($this)->getEffectiveFeatures();
    }

    /**
     * Get all effective features for the given model with the feature source(s).
     */
    public function getVerboseFeatures(): Collection
    {
        return Gatekeeper::for($this)->getVerboseFeatures();
    }
}
