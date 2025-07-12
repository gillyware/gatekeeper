<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Permission;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseStoreEntityRequest;
use Illuminate\Support\Facades\Config;

class StorePermissionRequest extends AbstractBaseStoreEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
    }
}
