<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class CreateTeamCommand extends Command
{
    protected $signature = 'gatekeeper:create-team {name}';

    protected $description = 'Create a new team';

    public function handle()
    {
        $name = $this->argument('name');
        $team = Gatekeeper::createTeam($name);

        $this->info("Team [{$team->name}] created.");
    }
}
