<?php

namespace Gillyware\Gatekeeper\Packets\Models;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Illuminate\Validation\Rule as ValidationRule;

final class ModelEntitiesPagePacket extends AbstractBaseModelPacket
{
    public function __construct(
        public readonly string $modelLabel,
        public readonly int|string $modelPk,
        #[Rule(['required', 'integer', 'min:1'])]
        public readonly int $page,
        #[Rule(['required', 'string'])]
        public readonly string $entity,
        #[Field('search_term'), Rule(['nullable', 'string'])]
        public readonly ?string $searchTerm,
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
