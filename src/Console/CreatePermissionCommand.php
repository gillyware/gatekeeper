<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CreatePermissionCommand extends Command
{
    protected $signature = 'gatekeeper:create-permission 
        {name : The name of the permission}
        {--action_by_model_id= : The ID of the actor model (for audit logging)}
        {--action_by_model_class=App\\Models\\User : The fully qualified class of the actor model}';

    protected $description = 'Create a new permission';

    public function handle()
    {
        $name = $this->argument('name');
        $actorId = $this->option('action_by_model_id');
        $actorClass = $this->option('action_by_model_class');

        if (Config::get('gatekeeper.features.audit')) {
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

        $permission = Gatekeeper::createPermission($name);
        $this->info("Permission [{$permission->name}] created.");

        return self::SUCCESS;
    }
}
