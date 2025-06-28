<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Support\SystemActor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CreateTeamCommand extends Command
{
    protected $signature = 'gatekeeper:create-team 
        {name : The name of the team}
        {--action_by_model_id= : The ID of the actor model (for audit logging)}
        {--action_by_model_class=App\\Models\\User : The fully qualified class of the actor model}';

    protected $description = 'Create a new team';

    public function handle(): int
    {
        $name = $this->argument('name');
        $actorId = $this->option('action_by_model_id');
        $actorClass = $this->option('action_by_model_class');

        if (Config::get('gatekeeper.features.audit')) {
            $actor = null;

            if ($actorId && $actorClass) {
                if (! class_exists($actorClass)) {
                    $this->error("Actor model class [$actorClass] does not exist.");

                    return self::FAILURE;
                }

                $actor = $actorClass::find($actorId);
                if (! $actor) {
                    $this->error("Actor [$actorClass] with ID [$actorId] not found.");

                    return self::FAILURE;
                }
            }

            if (! $actor) {
                $actor = new SystemActor;
                $this->info('No actor specified. This action will be attributed to the system.');
            }

            Gatekeeper::setActor($actor);
        }

        $team = Gatekeeper::createTeam($name);
        $this->info("[OK] Team [{$team->name}] created.");

        return self::SUCCESS;
    }
}
