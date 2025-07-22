<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of AbstractBaseEntityModel
 */
interface EntityRepositoryInterface
{
    /**
     * Check if the entities table exists.
     */
    public function tableExists(): bool;

    /**
     * Check if an entity with the given name exists.
     */
    public function exists(string $entityName): bool;

    /**
     * Find an entity by its name.
     *
     * @return ?TModel
     */
    public function findByName(string $entityName);

    /**
     * Find an entity by its name for a specific model.
     *
     * @return ?TModel
     */
    public function findByNameForModel(Model $model, string $entityName);

    /**
     * Find an entity by its name, or fail.
     *
     * @return TModel
     */
    public function findOrFailByName(string $entityName);

    /**
     * Create a new entity.
     *
     * @return TModel
     */
    public function create(string $entityName);

    /**
     * Update an existing entity.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function update($entity, string $newEntityName);

    /**
     * Deactivate an entity.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function deactivate($entity);

    /**
     * Reactivate an entity.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function reactivate($entity);

    /**
     * Delete an entity.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function delete($entity): bool;

    /**
     * Get all entities.
     *
     * @return Collection<TModel>
     */
    public function all(): Collection;

    /**
     * Get all active entities.
     *
     * @return Collection<TModel>
     */
    public function active(): Collection;

    /**
     * Get all entities where the name is in the provided array or collection.
     *
     * @return Collection<TModel>
     */
    public function whereNameIn(array|Collection $entityNames): Collection;

    /**
     * Get all entity names for a specific model.
     *
     * @return Collection<string>
     */
    public function namesForModel(Model $model): Collection;

    /**
     * Get all entities for a specific model.
     *
     * @return Collection<TModel>
     */
    public function forModel(Model $model): Collection;

    /**
     * Get all active entities for a specific model.
     *
     * @return Collection<TModel>
     */
    public function activeForModel(Model $model): Collection;

    /**
     * Get a page of entities.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator;
}
