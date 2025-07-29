<?php

namespace Gillyware\Gatekeeper\Packets\Entities;

use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Exceptions\PostalException;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;

abstract class AbstractBaseEntityPacket extends Packet
{
    public function __construct(
        #[Rule('required|integer|min:1')]
        public readonly int $id,

        #[Rule('required|string|max:255')]
        public readonly string $name,

        #[Field('is_active'), Rule('required|boolean')]
        public readonly bool $isActive,

        #[Field('grant_by_default'), Rule('required|boolean')]
        public readonly bool $grantedByDefault,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'grant_by_default' => $this->grantedByDefault,
        ];
    }

    protected static function failedValidation(Validator $validator): void
    {
        throw new PostalException('Malformed entity.');
    }
}
