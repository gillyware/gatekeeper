<?php

namespace Gillyware\Gatekeeper\Database\Seeders;

use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Database\Seeder;

class GatekeeperPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            GatekeeperPermissionName::View->value,
            GatekeeperPermissionName::Manage->value,
        ];

        foreach ($permissions as $permission) {
            Gatekeeper::systemActor()->createPermission($permission);
        }
    }
}
