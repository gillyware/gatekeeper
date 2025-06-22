<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchModelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $labels = app()->make(ModelMetadataService::class)->getConfiguredModelLabels();

        return [
            'model_label' => ['required', 'string', Rule::in($labels)],
            'search_term' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
