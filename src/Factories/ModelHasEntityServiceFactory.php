<?php

namespace Gillyware\Gatekeeper\Factories;

use Gillyware\Gatekeeper\Contracts\ModelHasEntityServiceInterface;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Services\ModelHasPermissionService;
use Gillyware\Gatekeeper\Services\ModelHasRoleService;
use Gillyware\Gatekeeper\Services\ModelHasTeamService;
use InvalidArgumentException;

class ModelHasEntityServiceFactory
{
    public static function create(GatekeeperEntity $entity): ModelHasEntityServiceInterface
    {
        return match ($entity) {
            GatekeeperEntity::Permission => app(ModelHasPermissionService::class),
            GatekeeperEntity::Role => app(ModelHasRoleService::class),
            GatekeeperEntity::Team => app(ModelHasTeamService::class),
            default => throw new InvalidArgumentException('Invalid entity.'),
        };
    }
}
