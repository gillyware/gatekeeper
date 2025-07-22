<?php

namespace Gillyware\Gatekeeper\Packets\Models;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;

final class ModelEntityPacket extends AbstractBaseModelPacket
{
    public function __construct(
        public readonly string $modelLabel,
        public readonly int|string $modelPk,
        #[Rule(['required', 'string', 'in:'.GatekeeperEntity::Permission->value.','.GatekeeperEntity::Role->value.','.GatekeeperEntity::Team->value])]
        public readonly string $entity,
        #[Field('entity_name'), Rule(['required', 'string'])]
        public readonly string $entityName,
    ) {}

    public function getEntity(): GatekeeperEntity
    {
        return GatekeeperEntity::from($this->entity);
    }
}
