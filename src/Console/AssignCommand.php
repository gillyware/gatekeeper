<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class AssignCommand extends Command
{
    protected $signature = 'gatekeeper:assign
        {--action_to_model_id= : The ID of the model to assign to}
        {--action_to_model_class=App\\Models\\User : The fully qualified class of the model}
        {--action_by_model_id= : The ID of the actor model (for audit logging)}
        {--action_by_model_class=App\\Models\\User : The fully qualified class of the actor model}
        {--role= : Role name (or comma-separated role names) to assign}
        {--permission= : Permission name (or comma-separated permission names) to assign}
        {--team= : Team name (or comma-separated team names) to assign}';

    protected $description = 'Assign one or multiple roles, permissions, and/or teams to a model.';

    public function handle(): int
    {
        $modelClass = $this->option('action_to_model_class');
        $modelId = $this->option('action_to_model_id');

        $actorClass = $this->option('action_by_model_class');
        $actorId = $this->option('action_by_model_id');

        if (Config::get('gatekeeper.features.audit')) {
            if ($actorClass && $actorId) {
                if (! class_exists($actorClass)) {
                    $this->components->error("[FAIL] Actor model class [$actorClass] does not exist.");

                    return self::FAILURE;
                }

                $actor = $actorClass::find($actorId);

                if (! $actor) {
                    $this->components->error("[FAIL] Actor model [$actorClass] with ID [$actorId] not found.");

                    return self::FAILURE;
                }

                Gatekeeper::setActor($actor);
            } else {
                $this->components->info('[INFO] No actor specified. This action will be attributed to the system.');

                Gatekeeper::systemActor();
            }
        }

        if (! $modelId || ! $modelClass) {
            $this->components->error('[FAIL] You must provide both --action_to_model_id and --action_to_model_class.');

            return self::FAILURE;
        }

        if (! class_exists($modelClass)) {
            $this->components->error("[FAIL] Model class [$modelClass] does not exist.");

            return self::FAILURE;
        }

        $model = $modelClass::find($modelId);

        if (! $model) {
            $this->components->error("[FAIL] Model [$modelClass] with ID [$modelId] not found.");

            return self::FAILURE;
        }

        $roles = array_filter(explode(',', (string) $this->option('role')));
        $permissions = array_filter(explode(',', (string) $this->option('permission')));
        $teams = array_filter(explode(',', (string) $this->option('team')));

        if (empty($roles) && empty($permissions) && empty($teams)) {
            $this->components->error('[FAIL] Please provide at least one of --role, --permission, or --team to assign.');

            return self::FAILURE;
        }

        foreach ($roles as $role) {
            Gatekeeper::assignRoleToModel($model, trim($role));
            $this->components->info("[OK] Assigned role: $role");
        }

        foreach ($permissions as $permission) {
            Gatekeeper::assignPermissionToModel($model, trim($permission));
            $this->components->info("[OK] Assigned permission: $permission");
        }

        foreach ($teams as $team) {
            Gatekeeper::addModelToTeam($model, trim($team));
            $this->components->info("[OK] Added to team: $team");
        }

        $this->newLine();
        $this->components->info('Done.');

        return self::SUCCESS;
    }
}
