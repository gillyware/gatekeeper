<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\TestCase;

class ListCommandTest extends TestCase
{
    public function test_lists_all_data_by_default()
    {
        Permission::factory()->create(['name' => 'edit-posts']);
        Role::factory()->create(['name' => 'admin']);
        Team::factory()->create(['name' => 'engineering']);

        $this->artisan('gatekeeper:list')
            ->expectsOutputToContain('Permissions')
            ->expectsOutputToContain('edit-posts')
            ->expectsOutputToContain('Roles')
            ->expectsOutputToContain('admin')
            ->expectsOutputToContain('Teams')
            ->expectsOutputToContain('engineering')
            ->assertExitCode(0);
    }

    public function test_lists_only_permissions_if_specified()
    {
        Permission::factory()->create(['name' => 'edit-posts']);

        $this->artisan('gatekeeper:list --permissions')
            ->expectsOutputToContain('Permissions')
            ->expectsOutputToContain('edit-posts')
            ->doesntExpectOutputToContain('Roles')
            ->doesntExpectOutputToContain('Teams')
            ->assertExitCode(0);
    }

    public function test_lists_only_roles_if_specified()
    {
        Role::factory()->create(['name' => 'admin']);

        $this->artisan('gatekeeper:list --roles')
            ->expectsOutputToContain('Roles')
            ->expectsOutputToContain('admin')
            ->doesntExpectOutputToContain('Permissions')
            ->doesntExpectOutputToContain('Teams')
            ->assertExitCode(0);
    }

    public function test_lists_only_teams_if_specified()
    {
        Team::factory()->create(['name' => 'engineering']);

        $this->artisan('gatekeeper:list --teams')
            ->expectsOutputToContain('Teams')
            ->expectsOutputToContain('engineering')
            ->doesntExpectOutputToContain('Permissions')
            ->doesntExpectOutputToContain('Roles')
            ->assertExitCode(0);
    }

    public function test_handles_empty_results_gracefully()
    {
        $this->artisan('gatekeeper:list')
            ->expectsOutput('No results found.')
            ->assertExitCode(0);
    }
}
