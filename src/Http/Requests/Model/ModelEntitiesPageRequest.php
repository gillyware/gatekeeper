<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Illuminate\Validation\Rule;

class ModelEntitiesPageRequest extends AbstractBaseModelRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['required', 'integer', 'min:1'],
            'entity' => ['required', 'string', Rule::in([GatekeeperEntity::Permission->value, GatekeeperEntity::Role->value, GatekeeperEntity::Team->value])],
            'search_term' => ['nullable', 'string'],
        ]);
    }
}
