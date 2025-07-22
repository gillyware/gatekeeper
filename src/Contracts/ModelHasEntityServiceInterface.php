<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Models\AbstractBaseModelHasEntityModel;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of AbstractBaseEntityModel
 * @template TModelHasEntity of AbstractBaseModelHasEntityModel
 */
interface ModelHasEntityServiceInterface
{
    /**
     * Search model entity assignments by entity name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;

    /**
     * Search unassigned entities by entity name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;
}
