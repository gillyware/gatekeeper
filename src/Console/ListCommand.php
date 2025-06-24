<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    protected $signature = 'gatekeeper:list
        {--permissions : Show only permissions}
        {--roles : Show only roles}
        {--teams : Show only teams}';

    protected $description = 'List Gatekeeper permissions, roles, and teams';

    public function handle(): int
    {
        $showPermissions = $this->option('permissions') || (! $this->option('roles') && ! $this->option('teams'));
        $showRoles = $this->option('roles') || (! $this->option('permissions') && ! $this->option('teams'));
        $showTeams = $this->option('teams') || (! $this->option('permissions') && ! $this->option('roles'));

        $hasOutput = false;

        if ($showPermissions) {
            $permissions = Permission::all(['id', 'name', 'is_active', 'created_at']);
            if ($permissions->isNotEmpty()) {
                $this->components->twoColumnDetail('Permissions', '');
                $this->table(['ID', 'Name', 'Active', 'Created'], $permissions->map(fn ($p) => [
                    $p->id, $p->name, $p->is_active ? 'Yes' : 'No', $p->created_at->toDateTimeString(),
                ]));
                $hasOutput = true;
            }
        }

        if ($showRoles) {
            $roles = Role::all(['id', 'name', 'is_active', 'created_at']);
            if ($roles->isNotEmpty()) {
                $this->components->twoColumnDetail('Roles', '');
                $this->table(['ID', 'Name', 'Active', 'Created'], $roles->map(fn ($r) => [
                    $r->id, $r->name, $r->is_active ? 'Yes' : 'No', $r->created_at->toDateTimeString(),
                ]));
                $hasOutput = true;
            }
        }

        if ($showTeams) {
            $teams = Team::all(['id', 'name', 'is_active', 'created_at']);
            if ($teams->isNotEmpty()) {
                $this->components->twoColumnDetail('Teams', '');
                $this->table(['ID', 'Name', 'Active', 'Created'], $teams->map(fn ($t) => [
                    $t->id, $t->name, $t->is_active ? 'Yes' : 'No', $t->created_at->toDateTimeString(),
                ]));
                $hasOutput = true;
            }
        }

        if (! $hasOutput) {
            $this->warn('No results found.');
        }

        return self::SUCCESS;
    }
}
