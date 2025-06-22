<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;

use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class ListCommand extends AbstractBaseGatekeeperCommand
{
    protected $signature = 'gatekeeper:list
        {--permissions : Show only permissions}
        {--roles : Show only roles}
        {--teams : Show only teams}';

    protected $description = 'List permissions, roles, and teams';

    public function handle(): int
    {
        $this->clearTerminal();

        $showPermissions = $this->option('permissions') || (! $this->option('roles') && ! $this->option('teams'));
        $showRoles = $this->option('roles') || (! $this->option('permissions') && ! $this->option('teams'));
        $showTeams = $this->option('teams') || (! $this->option('permissions') && ! $this->option('roles'));

        if ($showPermissions) {
            $permissions = Permission::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['id', 'name', 'is_active', 'created_at']);

            $this->info('Permissions');

            if ($permissions->isNotEmpty()) {
                table(['ID', 'Name', 'Active', 'Created'], $permissions->map(fn (Permission $p) => [
                    $p->id, $p->name, $p->is_active ? 'Yes' : 'No', $p->created_at->toDateTimeString(),
                ]));
            } else {
                warning('No permissions found.');
            }
        }

        if ($showRoles) {
            $roles = Role::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['id', 'name', 'is_active', 'created_at']);

            $this->info('Roles');

            if ($roles->isNotEmpty()) {
                table(['ID', 'Name', 'Active', 'Created'], $roles->map(fn (Role $r) => [
                    $r->id, $r->name, $r->is_active ? 'Yes' : 'No', $r->created_at->toDateTimeString(),
                ]));
            } else {
                warning('No roles found.');
            }
        }

        if ($showTeams) {
            $teams = Team::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['id', 'name', 'is_active', 'created_at']);

            $this->info('Teams');

            if ($teams->isNotEmpty()) {
                table(['ID', 'Name', 'Active', 'Created'], $teams->map(fn (Team $t) => [
                    $t->id, $t->name, $t->is_active ? 'Yes' : 'No', $t->created_at->toDateTimeString(),
                ]));
            } else {
                warning('No teams found.');
            }
        }

        return self::SUCCESS;
    }
}
