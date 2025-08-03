<?php

namespace Gillyware\Gatekeeper\Exceptions;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Illuminate\Database\Eloquent\Model;

class GatekeeperModelExceptionBuilder extends GatekeeperExceptionBuilder
{
    private string $className;

    public function notFound(string $class, int|string $pk): static
    {
        return $this->setMessage("Model with primary key '{$pk}' not found in class '{$class}'.");
    }

    public function missingHasPermissionsTrait(Model $model): static
    {
        return $this->setModel($model)->missingTrait(GatekeeperEntity::Permission);
    }

    public function missingHasRolesTrait(Model $model): static
    {
        return $this->setModel($model)->missingTrait(GatekeeperEntity::Role);
    }

    public function missingHasFeaturesTrait(Model $model): static
    {
        return $this->setModel($model)->missingTrait(GatekeeperEntity::Feature);
    }

    public function missingHasTeamsTrait(Model $model): static
    {
        return $this->setModel($model)->missingTrait(GatekeeperEntity::Team);
    }

    public function missingActor(): static
    {
        return $this->setMessage('The audit feature is enabled, but no acting as model is set.');
    }

    public function setModel(Model $model): static
    {
        $this->className = get_class($model);

        return $this;
    }

    private function missingTrait(GatekeeperEntity $entity): static
    {
        [$entity, $capitilizedEntity] = [$entity->value, ucfirst($entity->value)];

        return $this->setMessage("The model class [{$this->className}] cannot have {$entity}s. Consider using the [Gillyware\Gatekeeper\Traits\Has{$capitilizedEntity}s] trait in your model.");
    }
}
