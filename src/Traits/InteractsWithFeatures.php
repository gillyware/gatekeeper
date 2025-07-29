<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait InteractsWithFeatures
{
    /**
     * Get the features associated with the model.
     */
    public function features(): MorphToMany
    {
        $modelHasFeaturesTable = (new ModelHasFeature)->getTable();

        return $this->morphToMany(Feature::class, 'model', $modelHasFeaturesTable, 'model_id', 'feature_id');
    }
}
