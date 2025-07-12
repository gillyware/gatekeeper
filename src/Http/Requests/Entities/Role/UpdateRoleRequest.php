<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Role;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseUpdateEntityRequest;
use Illuminate\Support\Facades\Config;

class UpdateRoleRequest extends AbstractBaseUpdateEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
    }

    protected function getEntityId(): int
    {
        return (int) $this->route('role')?->id;
    }
}
