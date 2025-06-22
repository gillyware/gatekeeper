<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Team;

use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseUpdateEntityRequest;
use Illuminate\Support\Facades\Config;

class UpdateTeamRequest extends AbstractBaseUpdateEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.teams');
    }

    protected function getEntityId(): int
    {
        return (int) $this->route('team')?->id;
    }
}
