<?php

namespace Gillyware\Gatekeeper\Packets\Models;

use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractBaseModelPacket extends Packet
{
    public function __construct(
        public readonly string $modelLabel,
        public readonly int|string $modelPk,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }

    protected static function explicitRules(): array
    {
        $labels = app(ModelMetadataService::class)->getConfiguredModelLabels();

        return [
            'modelLabel' => ['required', 'string', Rule::in($labels)],
            'modelPk' => ['required', 'string', 'regex:/^[\w-]+$/'],
        ];
    }

    protected static function prepareForValidation(array $data): array
    {
        $routeParams = request()->route()?->parameters() ?? [];

        return $routeParams + $data;
    }
}
