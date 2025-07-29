<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseUpdateEntityPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @template TModel of AbstractBaseEntityModel
 * @template TPacket of AbstractBaseEntityPacket
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
     * @return TPacket
     */
    public function create(string|UnitEnum $entityName);

    /**
     * Update an existing entity.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @param  AbstractBaseUpdateEntityPacket  $packet
     * @return TPacket
     */
    public function update($entity, $packet);

    /**
     * Update an existing entity name.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function updateName($entity, string|UnitEnum $newEntityName);

    /**
     * Grant an entity to all models that are not explicitly denying it.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function grantByDefault($entity);

    /**
     * Revoke an entity's default grant.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function revokeDefaultGrant($entity);

    /**
     * Deactivate an entity.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function deactivate($entity);

    /**
     * Reactivate an entity.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function reactivate($entity);

    /**
     * Delete an entity.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function delete($entity): bool;

    /**
     * Assign an entity to a model.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function assignToModel(Model $model, $entity): bool;

    /**
     * Assign multiple entities to a model.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function assignAllToModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Unassign an entity from a model.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function unassignFromModel(Model $model, $entity): bool;

    /**
     * Unassign multiple entities from a model.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function unassignAllFromModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Deny an entity from a model.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function denyFromModel(Model $model, $entity): bool;

    /**
     * Deny multiple entities from a model.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function denyAllFromModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Remove a denial of an entity from a model.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function undenyFromModel(Model $model, $entity): bool;

    /**
     * Remove denials of multiple entities from a model.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function undenyAllFromModel(Model $model, array|Arrayable $entities): bool;

    /**
     * Check if a model has the given entity.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function modelHas(Model $model, $entity): bool;

    /**
     * Check if a model directly has the given entity (not granted through another entity).
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     */
    public function modelHasDirectly(Model $model, $entity): bool;

    /**
     * Check if a model has any of the given entities.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function modelHasAny(Model $model, array|Arrayable $entities): bool;

    /**
     * Check if a model has all of the given entities.
     *
     * @param  array<TModel|TPacket|string|UnitEnum>|Arrayable<TModel|TPacket|string|UnitEnum>  $entities
     */
    public function modelHasAll(Model $model, array|Arrayable $entities): bool;

    /**
     * Find an entity by its name.
     *
     * @return ?TPacket
     */
    public function findByName(string|UnitEnum $entityName);

    /**
     * Get all entities.
     *
     * @return Collection<string, TPacket>
     */
    public function getAll(): Collection;

    /**
     * Get all entities assigned directly or indirectly to a model.
     *
     * @return Collection<string, TPacket>
     */
    public function getForModel(Model $model): Collection;

    /**
     * Get all entities directly assigned to a model.
     *
     * @return Collection<string, TPacket>
     */
    public function getDirectForModel(Model $model): Collection;

    /**
     * Get all effective entities for the given model with the entity source(s).
     */
    public function getVerboseForModel(Model $model): Collection;

    /**
     * Get a page of entities.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator;
}
