<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class CreateRoleCommand extends Command
{
    protected $signature = 'gatekeeper:create-role {name}';

    protected $description = 'Create a new role';

    public function handle()
    {
        $name = $this->argument('name');
        $role = Gatekeeper::createRole($name);

        $this->info("Role [{$role->name}] created.");
    }
}
