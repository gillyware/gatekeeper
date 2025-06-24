<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Console\Command;

class RevokeCommand extends Command
{
    protected $signature = 'gatekeeper:revoke
        {--model_id= : The ID of the model to revoke from}
        {--model_class=App\\Models\\User : The fully qualified class of the model}
        {--role= : Role name (or comma-separated role names) to revoke}
        {--permission= : Permission name (or comma-separated permission names) to revoke}
        {--team= : Team name (or comma-separated team names) to remove the model from}';

    protected $description = 'Revoke one or multiple roles, permissions, and/or teams from a model.';

    public function handle(): int
    {
        $modelClass = $this->option('model_class');
        $modelId = $this->option('model_id');

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
            $this->error('Please provide at least one of --role, --permission, or --team to revoke.');

            return self::FAILURE;
        }

        foreach ($roles as $role) {
            Gatekeeper::revokeRoleFromModel($model, trim($role));
            $this->info("✅ Revoked role <fg=cyan>$role</> from model [$modelClass:$modelId]");
        }

        foreach ($permissions as $permission) {
            Gatekeeper::revokePermissionFromModel($model, trim($permission));
            $this->info("✅ Revoked permission <fg=yellow>$permission</> from model [$modelClass:$modelId]");
        }

        foreach ($teams as $team) {
            Gatekeeper::removeModelFromTeam($model, trim($team));
            $this->info("✅ Removed model [$modelClass:$modelId] from team <fg=green>$team</>");
        }

        $this->newLine();
        $this->components->info('Done!');

        return self::SUCCESS;
    }
}
