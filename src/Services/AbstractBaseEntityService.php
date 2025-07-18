<?php

namespace Gillyware\Gatekeeper\Services;

use BackedEnum;
use Gillyware\Gatekeeper\Contracts\EntityServiceInterface;
use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use UnitEnum;

use function Illuminate\Support\enum_value;

/**
 * @template TModel of AbstractBaseEntityModel
 *
 * @implements EntityServiceInterface<TModel>
 */
abstract class AbstractBaseEntityService implements EntityServiceInterface
{
    use EnforcesForGatekeeper;

    /**
     * Convert an array or Arrayable object of Gatekeeper entities or entity names to an array of entity names.
     */
    protected function entityNames(array|Arrayable $entities): Collection
    {
        $entityArray = $entities instanceof Arrayable ? $entities->toArray() : $entities;

        return collect($entityArray)->map(
            fn (AbstractBaseEntityModel|array|string|UnitEnum $entity) => $this->resolveEntityName($entity)
        );
    }

    /**
     * Resolve the Gatekeeper entity name from an entity, array, or string.
     */
    protected function resolveEntityName(AbstractBaseEntityModel|array|string|UnitEnum $entity): string
    {
        // If the entity is an instance of AbstractBaseEntityModel, return its name.
        if ($entity instanceof AbstractBaseEntityModel) {
            return $entity->name;
        }

        // If the entity is an enum, return the enum value.
        if ($entity instanceof BackedEnum || $entity instanceof UnitEnum) {
            return enum_value($entity);
        }

        // If the entity is a JSON string, decode it.
        if (is_string($entity) && Str::isJson($entity)) {
            $entity = json_decode($entity, true);
        }

        // If the entity is an array with a 'name' key, return the trimmed name.
        if (is_array($entity) && isset($entity['name']) && is_string($entity['name'])) {
            return trim($entity['name']);
        }

        // If the entity is a string, return it trimmed.
        if (is_string($entity)) {
            return trim($entity);
        }

        throw new InvalidArgumentException('Invalid entity type provided. Expected a Gatekeeper entity, array, or string.');
    }
}
