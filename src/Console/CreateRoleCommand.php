<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class CreateRoleCommand extends Command
{
    protected $signature = 'gatekeeper:create-role 
        {name : The name of the role}
        {--action_by_model_id= : The ID of the actor model (for audit logging)}
        {--action_by_model_class=App\\Models\\User : The fully qualified class of the actor model}';

    protected $description = 'Create a new role';

    public function handle()
    {
        $name = $this->argument('name');
        $actorId = $this->option('action_by_model_id');
        $actorClass = $this->option('action_by_model_class');

        if (config('gatekeeper.features.audit', true)) {
            if (! $actorId || ! $actorClass) {
                $this->error('Audit logging is enabled. You must provide --action_by_model_id and --action_by_model_class.');

                return self::FAILURE;
            }

            if (! class_exists($actorClass)) {
                $this->error("Actor model class [$actorClass] does not exist.");

                return self::FAILURE;
            }

            $actor = $actorClass::find($actorId);

            if (! $actor) {
                $this->error("Actor [$actorClass] with ID [$actorId] not found.");

                return self::FAILURE;
            }

            Gatekeeper::setActor($actor);
        }

        $role = Gatekeeper::createRole($name);
        $this->info("Role [{$role->name}] created.");

        return self::SUCCESS;
    }
}
