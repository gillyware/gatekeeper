<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractBaseEntityPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'important_attribute' => ['required', 'string', Rule::in(['name', 'is_active'])],
            'name_order' => ['required', 'string', Rule::in(['asc', 'desc'])],
            'is_active_order' => ['required', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
