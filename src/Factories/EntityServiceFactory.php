<?php

namespace Gillyware\Gatekeeper\Factories;

use Gillyware\Gatekeeper\Contracts\EntityServiceInterface;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Services\PermissionService;
use Gillyware\Gatekeeper\Services\RoleService;
use Gillyware\Gatekeeper\Services\TeamService;
use InvalidArgumentException;

class EntityServiceFactory
{
    public static function create(GatekeeperEntity $entity): EntityServiceInterface
    {
        return match ($entity) {
            GatekeeperEntity::Permission => app(PermissionService::class),
            GatekeeperEntity::Role => app(RoleService::class),
            GatekeeperEntity::Team => app(TeamService::class),
            default => throw new InvalidArgumentException('Invalid entity.'),
        };
    }
}
