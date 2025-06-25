<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Models\AbstractGatekeeperEntity;
use Braxey\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class AbstractGatekeeperEntityService
{
    use EnforcesForGatekeeper;

    /**
     * Convert an array or Arrayable object of Gatekeeper entities, entity names, or an array to an array of entity names.
     */
    protected function entityNamesArray(array|Arrayable $entities): array
    {
        $entityArray = $entities instanceof Arrayable ? $entities->toArray() : $entities;

        return array_map(function (AbstractGatekeeperEntity|array|string $entity) {
            return $this->resolveEntityName($entity);
        }, $entityArray);
    }

    /**
     * Resolve the Gatekeeper entity name from an entity, array, or string.
     */
    protected function resolveEntityName(AbstractGatekeeperEntity|array|string $entity): string
    {
        // If the entity is a string, check if it's JSON or a simple string.
        if (is_string($entity)) {
            if (Str::isJson($entity)) {
                $entity = json_decode($entity, true);
            } else {
                return trim($entity);
            }
        }

        // If the entity is an array, check for a 'name' key.
        if (is_array($entity)) {
            if (isset($entity['name']) && is_string($entity['name'])) {
                return trim($entity['name']);
            }

            throw new InvalidArgumentException('Invalid entity array: '.json_encode($entity));
        }

        return $entity->name;
    }
}
