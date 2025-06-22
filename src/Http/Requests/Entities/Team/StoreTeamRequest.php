<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Team;

use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseStoreEntityRequest;
use Illuminate\Support\Facades\Config;

class StoreTeamRequest extends AbstractBaseStoreEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.teams');
    }
}
