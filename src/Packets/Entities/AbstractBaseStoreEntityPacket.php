<?php

namespace Gillyware\Gatekeeper\Packets\Entities;

use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractBaseStoreEntityPacket extends Packet
{
    public function __construct(
        #[Rule(['required', 'string', 'max:255'])]
        public readonly string $name,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    protected static function explicitRules(): array
    {
        return [
            'name' => [
                ValidationRule::unique(static::getTableName(), 'name')->withoutTrashed(),
            ],
        ];
    }

    abstract protected static function getTableName(): string;
}
