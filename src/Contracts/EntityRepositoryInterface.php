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
     * Get all entities.
     *
     * @return Collection<string, TModel>
     */
    public function all(): Collection;

    /**
     * Find an entity by its name.
     *
     * @return ?TModel
     */
    public function findByName(string $entityName);

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
     * Update an existing entity name.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function updateName($entity, string $newEntityName);

    /**
     * Grant an entity to all models that are not explicitly denying it.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function grantByDefault($entity);

    /**
     * Revoke an entity's default grant.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function revokeDefaultGrant($entity);

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
     * Get all entities assigned to a specific model.
     *
     * @return Collection<string, TModel>
     */
    public function assignedToModel(Model $model): Collection;

    /**
     * Get all entities denied from a specific model.
     *
     * @return Collection<string, TModel>
     */
    public function deniedFromModel(Model $model): Collection;

    /**
     * Get a page of entities.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator;
}
