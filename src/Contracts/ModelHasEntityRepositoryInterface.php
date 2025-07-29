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
     * Assign an entity to a model.
     *
     * @param  TModel  $entity
     * @return TModelHasEntity
     */
    public function assignToModel(Model $model, $entity);

    /**
     * Delete all non-denied entity assignments for a given model and entity.
     *
     * @param  TModel  $entity
     */
    public function unassignFromModel(Model $model, $entity): bool;

    /**
     * Deny an entity from a model.
     *
     * @param  TModel  $entity
     * @return TModelHasEntity
     */
    public function denyFromModel(Model $model, $entity);

    /**
     * Delete all denied entity assignments for a given model and entity.
     *
     * @param  TModel  $entity
     * @return TModelHasEntity
     */
    public function undenyFromModel(Model $model, $entity);

    /**
     * Delete all entity assignments and denials for a given model.
     */
    public function deleteForModel(Model $model): bool;

    /**
     * Delete all assignments for a given entity.
     *
     * @param  TModel  $entity
     */
    public function deleteForEntity($entity): bool;

    /**
     * Search model entity assignments by entity name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;

    /**
     * Search unassigned entities by entity name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;

    /**
     * Search denied entities by entity name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator;
}
