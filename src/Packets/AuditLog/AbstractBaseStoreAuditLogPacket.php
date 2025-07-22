<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Closure;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule as ValidationRule;
use ReflectionClass;

abstract class AbstractBaseStoreAuditLogPacket extends Packet
{
    public function __construct(
        #[Rule(['required', 'string'])]
        public readonly string $action,

        #[Field('action_by_model_type'), Rule(['nullable', 'string'])]
        public readonly ?string $actionByModelType,

        #[Field('action_by_model_id'), Rule(['nullable'])]
        public readonly int|string|null $actionByModelId,

        #[Field('action_to_model_type'), Rule(['nullable', 'string'])]
        public readonly ?string $actionToModelType,

        #[Field('action_to_model_id'), Rule(['nullable'])]
        public readonly int|string|null $actionToModelId,

        #[Rule(['nullable', 'array'])]
        public readonly ?array $metadata,
    ) {}

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'action_by_model_type' => $this->actionByModelType,
            'action_by_model_id' => $this->actionByModelId,
            'action_to_model_type' => $this->actionToModelType,
            'action_to_model_id' => $this->actionToModelId,
            'metadata' => $this->metadata,
        ];
    }

    protected static function prepareForValidation(array $data): array
    {
        /** @var ?Model $actionBy */
        $actionBy = Gatekeeper::getActor();
        /** @var ?Model $actionTo */
        $actionTo = data_get($data, 'action_to');
        $metadata = data_get($data, 'metadata', []);

        return [
            'action' => static::getAction()->value,
            'action_by_model_type' => $actionBy?->getMorphClass(),
            'action_by_model_id' => $actionBy?->getKey(),
            'action_to_model_type' => $actionTo?->getMorphClass(),
            'action_to_model_id' => $actionTo?->getKey(),
            'metadata' => array_merge($metadata, [
                'lifecycle_id' => Gatekeeper::getLifecycleId(),
            ]),
        ];
    }

    protected static function explicitRules(): array
    {
        $allowedActions = array_column(AuditLogAction::cases(), 'value');

        $validModelTypeRule = function (string $attr, string $value, Closure $fail): void {
            if (! class_exists($value)) {
                $fail("Class {$value} does not exist.");

                return;
            }
            if (! is_subclass_of($value, Model::class)) {
                $fail("{$value} is not an Eloquent model.");
            }
            if ((new ReflectionClass($value))->isAbstract()) {
                $fail("Class {$value} is abstract.");
            }
        };

        return [
            'action' => [ValidationRule::in($allowedActions)],
            'action_by_model_type' => [$validModelTypeRule],
            'action_to_model_type' => [$validModelTypeRule],
        ];
    }

    abstract protected static function getAction(): AuditLogAction;
}
