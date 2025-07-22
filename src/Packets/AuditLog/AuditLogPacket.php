<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Exceptions\PostalException;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;

final class AuditLogPacket extends Packet
{
    public function __construct(
        #[Rule('required|integer|min:1')]
        public readonly int $id,

        #[Rule('required|string')]
        public readonly string $message,

        #[Field('created_at'), Rule('string|date_format:Y-m-d H:i:s T')]
        public readonly string $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'created_at' => $this->createdAt,
        ];
    }

    protected static function failedValidation(Validator $validator): void
    {
        throw new PostalException('Malformed audit log.');
    }
}
