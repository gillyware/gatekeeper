<?php

namespace Braxey\Gatekeeper\Database\Seeders;

use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Seeder;

class GatekeeperPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'gatekeeper.view',
            'gatekeeper.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
