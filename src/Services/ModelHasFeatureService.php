<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityServiceInterface;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Repositories\ModelHasFeatureRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class ModelHasFeatureService implements ModelHasEntityServiceInterface
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasFeatureRepository $modelHasFeatureRepository,
    ) {}

    /**
     * Search model feature assignments by feature name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $paginator = $this->modelHasFeatureRepository->searchAssignmentsByEntityNameForModel($model, $packet);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasFeature $modelHasFeature) use ($displayTimezone) {
            $modelHasFeature->offsetSet('assigned_at', $modelHasFeature->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

            return $modelHasFeature;
        });
    }

    /**
     * Search unassigned features by feature name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return $this->modelHasFeatureRepository->searchUnassignedByEntityNameForModel($model, $packet)
            ->through(fn (Feature $feature) => $feature->toPacket());
    }
}
