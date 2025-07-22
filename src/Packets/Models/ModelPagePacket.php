<?php

namespace Gillyware\Gatekeeper\Packets\Models;

use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Postal\Attributes\Field;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ModelPagePacket extends Packet
{
    public function __construct(
        #[Field('model_label'), Rule(['required', 'string'])]
        public readonly string $modelLabel,

        #[Field('search_term'), Rule(['nullable', 'string'])]
        public readonly ?string $searchTerm,
    ) {}

    protected static function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }

    protected static function explicitRules(): array
    {
        $labels = app(ModelMetadataService::class)->getConfiguredModelLabels();

        return [
            'model_label' => [ValidationRule::in($labels)],
        ];
    }
}
