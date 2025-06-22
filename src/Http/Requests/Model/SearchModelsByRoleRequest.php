<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

class SearchModelsByRoleRequest extends SearchModelsRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['required', 'integer', 'min:1'],
            'role_name_search_term' => ['nullable', 'string'],
        ]);
    }
}
