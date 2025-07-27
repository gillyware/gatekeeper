<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\Feature;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;

trait InteractsWithFeatures
{
    /**
     * Get the features associated with the model.
     */
    public function features(): MorphToMany
    {
        $modelHasFeaturesTable = Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES);

        return $this->morphToMany(Feature::class, 'model', $modelHasFeaturesTable, 'model_id', 'feature_id');
    }
}
