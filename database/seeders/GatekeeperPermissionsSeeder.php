<?php

namespace Gillyware\Gatekeeper\Database\Seeders;

use Gillyware\Gatekeeper\Constants\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Database\Seeder;

class GatekeeperPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            GatekeeperPermissionName::VIEW,
            GatekeeperPermissionName::MANAGE,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
