<?php

namespace Gillyware\Gatekeeper\Packets\Entities;

use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractBaseUpdateEntityPacket extends Packet
{
    public function __construct(
        #[Rule(['string', 'max:255'])]
        public readonly string $action,

        #[Rule('required')]
        public readonly string|bool $value,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    protected static function explicitRules(): array
    {
        $allowedActions = array_column(EntityUpdateAction::cases(), 'value');
        $action = request('action');

        return [
            'action' => [ValidationRule::in($allowedActions)],
            'value' => match ($action) {
                EntityUpdateAction::Name->value => [
                    ValidationRule::unique(static::getTableName(), 'name')->withoutTrashed()->ignore(static::getEntityId()),
                ],
                EntityUpdateAction::Status->value, EntityUpdateAction::DefaultGrant->value => ['boolean'],
                default => [],
            },

        ];
    }

    abstract protected static function getTableName(): string;

    abstract protected static function getEntityId(): int;
}
