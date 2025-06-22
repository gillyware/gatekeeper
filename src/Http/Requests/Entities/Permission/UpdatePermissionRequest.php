<?php

namespace Gillyware\Gatekeeper\Http\Requests\Entities\Permission;

use Gillyware\Gatekeeper\Http\Requests\Entities\AbstractBaseUpdateEntityRequest;
use Illuminate\Support\Facades\Config;

class UpdatePermissionRequest extends AbstractBaseUpdateEntityRequest
{
    protected function getTableName(): string
    {
        return Config::get('gatekeeper.tables.permissions');
    }

    protected function getEntityId(): int
    {
        return (int) $this->route('permission')?->id;
    }
}
