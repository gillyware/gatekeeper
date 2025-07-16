<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
use Illuminate\Validation\Rule;

class ModelEntityRequest extends AbstractBaseModelRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'entity' => ['required', 'string', Rule::in([GatekeeperEntity::PERMISSION, GatekeeperEntity::ROLE, GatekeeperEntity::TEAM])],
            'entity_name' => ['required', 'string'],
        ]);
    }
}
