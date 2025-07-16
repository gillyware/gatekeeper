<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Illuminate\Validation\Rule;

class ModelEntityRequest extends AbstractBaseModelRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'entity' => ['required', 'string', Rule::in([GatekeeperEntity::Permission->value, GatekeeperEntity::Role->value, GatekeeperEntity::Team->value])],
            'entity_name' => ['required', 'string'],
        ]);
    }
}
