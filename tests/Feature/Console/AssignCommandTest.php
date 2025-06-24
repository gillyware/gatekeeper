<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class AssignCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles', true);
        Config::set('gatekeeper.features.teams', true);
    }

    public function test_assigns_single_role_permission_and_team()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();

        Artisan::call('gatekeeper:assign', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--role' => $role->name,
            '--permission' => $permission->name,
            '--team' => $team->name,
        ]);

        $this->assertTrue($user->roles->contains($role));
        $this->assertTrue($user->permissions->contains($permission));
        $this->assertTrue($user->teams->contains($team));
    }

    public function test_assigns_multiple_roles_permissions_and_teams()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $permissions = Permission::factory()->count(2)->create();
        $teams = Team::factory()->count(2)->create();

        $roleNames = $roles->pluck('name')->implode(',');
        $permissionNames = $permissions->pluck('name')->implode(',');
        $teamNames = $teams->pluck('name')->implode(',');

        Artisan::call('gatekeeper:assign', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--role' => $roleNames,
            '--permission' => $permissionNames,
            '--team' => $teamNames,
        ]);

        foreach ($roles as $role) {
            $this->assertTrue($user->roles->contains($role));
        }

        foreach ($permissions as $permission) {
            $this->assertTrue($user->permissions->contains($permission));
        }

        foreach ($teams as $team) {
            $this->assertTrue($user->teams->contains($team));
        }
    }

    public function test_fails_if_model_class_does_not_exist()
    {
        $exitCode = Artisan::call('gatekeeper:assign', [
            '--model_id' => 1,
            '--model_class' => 'Fake\\Class',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('does not exist', Artisan::output());
    }

    public function test_fails_if_model_is_not_found()
    {
        $exitCode = Artisan::call('gatekeeper:assign', [
            '--model_id' => 999,
            '--model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('not found', Artisan::output());
    }

    public function test_fails_if_nothing_to_assign()
    {
        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Please provide at least one of', Artisan::output());
    }
}
