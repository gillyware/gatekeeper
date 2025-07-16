<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
use Illuminate\Validation\Rule;

class ModelEntitiesPageRequest extends AbstractBaseModelRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['required', 'integer', 'min:1'],
            'entity' => ['required', 'string', Rule::in([GatekeeperEntity::PERMISSION, GatekeeperEntity::ROLE, GatekeeperEntity::TEAM])],
            'search_term' => ['nullable', 'string'],
        ]);
    }
}
