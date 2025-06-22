<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractBaseUpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = $this->getTableName();
        $entityId = $this->getEntityId();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique($tableName, 'name')->withoutTrashed()->ignore($entityId)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    abstract protected function getTableName(): string;

    abstract protected function getEntityId(): int;
}
