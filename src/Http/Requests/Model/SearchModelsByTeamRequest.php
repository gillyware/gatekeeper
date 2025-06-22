<?php

namespace Gillyware\Gatekeeper\Http\Requests\Model;

class SearchModelsByTeamRequest extends SearchModelsRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'page' => ['required', 'integer', 'min:1'],
            'team_name_search_term' => ['nullable', 'string'],
        ]);
    }
}
