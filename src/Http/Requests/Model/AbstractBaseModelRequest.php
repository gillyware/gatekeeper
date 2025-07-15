<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AbstractBaseModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return array_merge($this->all(), $this->route()->parameters());
    }

    public function rules(): array
    {
        $labels = app()->make(ModelMetadataService::class)->getConfiguredModelLabels();

        return [
            'modelLabel' => ['required', 'string', Rule::in($labels)],
            'modelPk' => ['required', 'string', 'regex:/^[\w-]+$/'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
