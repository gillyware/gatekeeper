<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class AssignCommand extends Command
{
    protected $signature = 'gatekeeper:assign
        {--model_id= : The ID of the model to assign to}
        {--model_class=App\\Models\\User : The fully qualified class of the model}
        {--role= : Role name (or comma-separated role names) to assign}
        {--permission= : Permission name (or comma-separated permission names) to assign}
        {--team= : Team name (or comma-separated team names) to assign}';

    protected $description = 'Assign one or multiple roles, permissions, and/or teams to a model.';

    public function handle(): int
    {
        $modelClass = $this->option('model_class');
        $modelId = $this->option('model_id');
        $role = $this->option('role');
        $permission = $this->option('permission');
        $team = $this->option('team');

        if (! $modelId || ! $modelClass) {
            $this->error('You must provide both --model_id and --model_class.');

            return self::FAILURE;
        }

        if (! class_exists($modelClass)) {
            $this->error("Model class [$modelClass] does not exist.");

            return self::FAILURE;
        }

        $model = $modelClass::find($modelId);

        if (! $model) {
            $this->error("Model [$modelClass] with ID [$modelId] not found.");

            return self::FAILURE;
        }

        $roles = array_filter(explode(',', (string) $this->option('role')));
        $permissions = array_filter(explode(',', (string) $this->option('permission')));
        $teams = array_filter(explode(',', (string) $this->option('team')));

        if (empty($roles) && empty($permissions) && empty($teams)) {
            $this->error('Please provide at least one of --role, --permission, or --team to assign.');

            return self::FAILURE;
        }

        foreach ($roles as $role) {
            Gatekeeper::assignRoleToModel($model, trim($role));
            $this->info("✅ Assigned role <fg=cyan>$role</> to model [$modelClass:$modelId]");
        }

        foreach ($permissions as $permission) {
            Gatekeeper::assignPermissionToModel($model, trim($permission));
            $this->info("✅ Assigned permission <fg=yellow>$permission</> to model [$modelClass:$modelId]");
        }

        foreach ($teams as $team) {
            Gatekeeper::addModelToTeam($model, trim($team));
            $this->info("✅ Added model [$modelClass:$modelId] to team <fg=green>$team</>");
        }

        $this->newLine();
        $this->components->info('Done!');

        return self::SUCCESS;
    }
}
