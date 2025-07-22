<?php

namespace Gillyware\Gatekeeper\Packets\Models;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Illuminate\Validation\Rule as ValidationRule;

final class ModelEntityPacket extends AbstractBaseModelPacket
{
    public function __construct(
        public readonly string $modelLabel,
        public readonly int|string $modelPk,
        #[Rule(['required', 'string'])]
        public readonly string $entity,
        #[Field('entity_name'), Rule(['required', 'string'])]
        public readonly string $entityName,
    ) {}

    public function getEntity(): GatekeeperEntity
    {
        return GatekeeperEntity::from($this->entity);
    }

    protected static function explicitRules(): array
    {
        $allowedEntities = array_column(GatekeeperEntity::cases(), 'value');

        return array_merge(parent::explicitRules(), [
            'entity' => [ValidationRule::in($allowedEntities)],
        ]);
    }
}
