<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Support\Facades\Config;

use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class ListCommand extends AbstractBaseGatekeeperCommand
{
    protected $signature = 'gatekeeper:list
        {--permissions : Filter for permissions}
        {--roles : Filter for roles}
        {--features : Filter for features}
        {--teams : Filter for teams}';

    protected $description = 'List permissions, roles, features, and teams';

    public function handle(): int
    {
        $this->clearTerminal();

        $showPermissions = $this->option('permissions') || (! $this->option('roles') && ! $this->option('features') && ! $this->option('teams'));
        $showRoles = $this->option('roles') || (! $this->option('permissions') && ! $this->option('features') && ! $this->option('teams'));
        $showFeatures = $this->option('features') || (! $this->option('permissions') && ! $this->option('roles') && ! $this->option('teams'));
        $showTeams = $this->option('teams') || (! $this->option('permissions') && ! $this->option('roles') && ! $this->option('features'));
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        if ($showPermissions) {
            $permissions = Permission::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['name', 'is_active', 'created_at', 'updated_at']);

            $this->info('Permissions');

            if ($permissions->isNotEmpty()) {
                table(['Name', 'Active', 'Created', 'Updated'], $permissions->map(fn (Permission $p) => [
                    $p->name, $p->is_active ? 'Yes' : 'No', $p->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'), $p->updated_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'),
                ]));
            } else {
                warning('No permissions found.');
            }
        }

        if ($showRoles) {
            $roles = Role::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['name', 'is_active', 'created_at', 'updated_at']);

            $this->info('Roles');

            if ($roles->isNotEmpty()) {
                table(['Name', 'Active', 'Created', 'Updated'], $roles->map(fn (Role $r) => [
                    $r->name, $r->is_active ? 'Yes' : 'No', $r->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'), $r->updated_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'),
                ]));
            } else {
                warning('No roles found.');
            }
        }

        if ($showFeatures) {
            $features = Feature::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['name', 'is_active', 'default_enabled', 'created_at', 'updated_at']);

            $this->info('Features');

            if ($features->isNotEmpty()) {
                table(['Name', 'Active', 'Default', 'Created', 'Updated'], $features->map(fn (Feature $f) => [
                    $f->name, $f->is_active ? 'Yes' : 'No', $f->default_enabled ? 'On' : 'Off', $f->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'), $f->updated_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'),
                ]));
            } else {
                warning('No features found.');
            }
        }

        if ($showTeams) {
            $teams = Team::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['name', 'is_active', 'created_at', 'updated_at']);

            $this->info('Teams');

            if ($teams->isNotEmpty()) {
                table(['Name', 'Active', 'Created', 'Updated'], $teams->map(fn (Team $t) => [
                    $t->name, $t->is_active ? 'Yes' : 'No', $t->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'), $t->updated_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'),
                ]));
            } else {
                warning('No teams found.');
            }
        }

        return self::SUCCESS;
    }
}
