<?php

namespace Gillyware\Gatekeeper\Database\Seeders;

use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Database\Seeder;

class GatekeeperPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            GatekeeperPermission::View->value,
            GatekeeperPermission::Manage->value,
        ];

        foreach ($permissions as $permission) {
            Gatekeeper::systemActor()->createPermission($permission);
        }
    }
}
