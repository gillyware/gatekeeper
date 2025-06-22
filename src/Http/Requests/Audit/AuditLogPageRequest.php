<?php

namespace Gillyware\Gatekeeper\Http\Requests\Audit;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuditLogPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'created_at_order' => ['string', 'required', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException($validator->errors()->toJson());
    }
}
