<?php

namespace Gillyware\Gatekeeper\Packets\Entities;

use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EntityPagePacket extends Packet
{
    public function __construct(
        #[Rule(['required', 'integer', 'min:1'])]
        public readonly int $page,

        #[Field('search_term'), Rule(['present', 'nullable', 'string'])]
        public readonly ?string $searchTerm,

        #[Field('prioritized_attribute'), Rule(['required', 'string', 'in:name,is_active'])]
        public readonly string $prioritizedAttribute,

        #[Field('name_order'), Rule(['required', 'string', 'in:asc,desc'])]
        public readonly string $nameOrder,

        #[Field('is_active_order'), Rule(['required', 'string', 'in:asc,desc'])]
        public readonly string $isActiveOrder,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
