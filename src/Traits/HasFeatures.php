<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasFeatures
{
    use InteractsWithFeatures, InteractsWithTeams;

    /**
     * Assign a feature to the model.
     */
    public function assignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->assignFeature($feature);
    }

    /**
     * Assign multiple features to the model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function assignAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->assignAllFeatures($features);
    }

    /**
     * Unassign a feature from the model.
     */
    public function unassignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->unassignFeature($feature);
    }

    /**
     * Unassign multiple features from the model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function unassignAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->unassignAllFeatures($features);
    }

    /**
     * Deny a feature from the model.
     */
    public function denyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->denyFeature($feature);
    }

    /**
     * Deny multiple features from the model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function denyAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->denyAllFeatures($features);
    }

    /**
     * Undeny a feature from the model.
     */
    public function undenyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->undenyFeature($feature);
    }

    /**
     * Undeny multiple features from the model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function undenyAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->undenyAllFeatures($features);
    }

    /**
     * Check if the model has a given feature.
     */
    public function hasFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::for($this)->hasFeature($feature);
    }

    /**
     * Check if the model has any of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function hasAnyFeature(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->hasAnyFeature($features);
    }

    /**
     * Check if the model has all of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function hasAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::for($this)->hasAllFeatures($features);
    }

    /**
     * Get all features assigned directly to a model.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getDirectFeatures(): Collection
    {
        return Gatekeeper::for($this)->getDirectFeatures();
    }

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<string, FeaturePacket>
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
