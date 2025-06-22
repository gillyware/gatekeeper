<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

class SearchModelsByPermissionRequest extends SearchModelsRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['required', 'integer', 'min:1'],
            'permission_name_search_term' => ['nullable', 'string'],
        ]);
    }
}
