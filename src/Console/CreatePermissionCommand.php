<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class CreatePermissionCommand extends Command
{
    protected $signature = 'gatekeeper:create-permission {name}';

    protected $description = 'Create a new permission';

    public function handle()
    {
        $name = $this->argument('name');
        $permission = Gatekeeper::createPermission($name);

        $this->info("Permission [{$permission->name}] created.");
    }
}
