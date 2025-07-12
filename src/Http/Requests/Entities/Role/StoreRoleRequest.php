<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Role;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseStoreEntityRequest;
use Illuminate\Support\Facades\Config;

class StoreRoleRequest extends AbstractBaseStoreEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }
}
