<?php

namespace Gillyware\Gatekeeper\Exceptions;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;

class GatekeeperEntityExceptionBuilder extends GatekeeperExceptionBuilder
{
    private GatekeeperEntity $entity;

    public function featureDisabled(): static
    {
        return $this->setMessage("The {$this->entity->value}s feature is disabled. Please enable it in the configuration.");
    }

    public function notFound(string $entityName): static
    {
        $entity = ucfirst($this->entity->value);

        return $this->setMessage("{$entity} '{$entityName}' not found.");
    }

    public function alreadyExists(string $entityName): static
    {
        $entity = ucfirst($this->entity->value);

        return $this->setMessage("{$entity} '{$entityName}' already exists.");
    }

    public function cannotUnassignFromSelf(string $entityName): static
    {
        return $this->setMessage("You may not unassign {$this->entity->value} '$entityName' from yourself.");
    }

    public function setEntity(GatekeeperEntity $entity): static
    {
        $this->entity = $entity;

        return $this;
    }
}
