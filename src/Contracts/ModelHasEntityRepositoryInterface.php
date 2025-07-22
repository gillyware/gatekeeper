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
interface ModelHasEntityRepositoryInterface
{
    /**
     * Check if an entity is assigned to any model.
     *
     * @param  TModel  $entity
     */
    public function existsForEntity($entity): bool;

    /**
     * Create a new model entity assigment.
     *
     * @param  TModel  $entity
     * @return TModelHasEntity
     */
    public function create(Model $model, $entity);

    /**
     * Delete all entity assignments for a given model.
     */
    public function deleteForModel(Model $model): bool;

    /**
     * Delete all assignments for a given entity.
     *
     * @param  TModel  $entity
     */
    public function deleteForEntity($entity): bool;

    /**
     * Delete all entity assignments for a given model and entity.
     *
     * @param  TModel  $entity
     */
    public function deleteForModelAndEntity(Model $model, $entity): bool;

    /**
     * Search model entity assignments by entity name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;

    /**
     * Search unassigned entities by entity name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;
}
