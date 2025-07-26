<?php

namespace Gillyware\Gatekeeper\Packets\Entities\Feature;

use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;

final class FeaturePacket extends AbstractBaseEntityPacket
{
    public function __construct(
        #[Rule('required|integer|min:1')]
        public readonly int $id,

        #[Rule('required|string|max:255')]
        public readonly string $name,

        #[Field('is_active'), Rule('required|boolean')]
        public readonly bool $isActive,

        #[Field('default_enabled'), Rule('required|boolean')]
        public readonly bool $enabledByDefault,
    ) {}

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'default_enabled' => $this->enabledByDefault,
        ]);
    }
}
