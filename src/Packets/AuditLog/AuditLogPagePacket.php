<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AuditLogPagePacket extends Packet
{
    public function __construct(
        #[Rule(['required', 'integer', 'min:1'])]
        public readonly int $page,

        #[Field('created_at_order'), Rule(['required', 'string', 'in:asc,desc'])]
        public readonly string $createdAtOrder,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
