<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @template TModel of AbstractBaseEntityModel
 */
interface EntityServiceInterface
{
    /**
     * Check if the entities table exists.
     */
    public function tableExists(): bool;

    /**
     * Check if an entity with the given name exists.
     */
    public function exists(string|UnitEnum $entityName): bool;

    /**
     * Create a new entity.
     *
     * @return TModel
     */
    public function create(string|UnitEnum $entityName);

    /**
     * Update an existing entity.
     *
     * @param  TModel|string|UnitEnum  $entity
     * @return TModel
     */
    public function update($entity, string|UnitEnum $newEntityName);

    /**
     * Deactivate an entity.
     *
     * @param  TModel|string|UnitEnum  $entity
     * @return TModel
     */
    public function deactivate($entity);

    /**
     * Reactivate an entity.
     *
     * @param  TModel|string|UnitEnum  $entity
     * @return TModel
     */
    public function reactivate($entity);

    /**
     * Delete an entity.
     *
     * @param  TModel|string|UnitEnum  $entity
     */
    public function delete($entity): bool;

    /**
     * Assign an entity to a model.
     *
     * @param  TModel|string|UnitEnum  $entity
     */
    public function assignToModel(Model $model, $entity): bool;

    /**
     * Assign multiple entities to a model.
     *
     * @param  array<TModel|string|UnitEnum>|Arrayable<TModel|string|UnitEnum>  $entities
     */
    public function assignAllToModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Revoke an entity from a model.
     *
     * @param  TModel|string|UnitEnum  $entity
     */
    public function revokeFromModel(Model $model, $entity): bool;

    /**
     * Revoke multiple entities from a model.
     *
     * @param  array<TModel|string|UnitEnum>|Arrayable<TModel|string|UnitEnum>  $entities
     */
    public function revokeAllFromModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Check if a model has the given entity.
     *
     * @param  TModel|string|UnitEnum  $entity
     */
    public function modelHas(Model $model, $entity): bool;

    /**
     * Check if a model directly has the given entity (not granted through another entity).
     *
     * @param  TModel|string|UnitEnum  $entity
     */
    public function modelHasDirectly(Model $model, $entity): bool;

    /**
     * Check if a model has any of the given entities.
     *
     * @param  array<TModel|string|UnitEnum>|Arrayable<TModel|string|UnitEnum>  $entities
     */
    public function modelHasAny(Model $model, array|Arrayable $entities): bool;

    /**
     * Check if a model has all of the given entities.
     *
     * @param  array<TModel|string|UnitEnum>|Arrayable<TModel|string|UnitEnum>  $entities
     */
    public function modelHasAll(Model $model, array|Arrayable $entities): bool;

    /**
     * Find an entity by its name.
     *
     * @return ?TModel
     */
    public function findByName(string|UnitEnum $entityName);

    /**
     * Get all entities.
     *
     * @return Collection<TModel>
     */
    public function getAll(): Collection;

    /**
     * Get all entities assigned directly or indirectly to a model.
     *
     * @return Collection<TModel>
     */
    public function getForModel(Model $model): Collection;

    /**
     * Get all entities directly assigned to a model.
     *
     * @return Collection<TModel>
     */
    public function getDirectForModel(Model $model): Collection;

    /**
     * Get a page of entities.
     */
    public function getPage(EntityPagePacket $entityPagePacket): LengthAwarePaginator;
}
