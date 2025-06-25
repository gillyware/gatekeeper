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

    public function test_assigns_single_role_permission_and_team(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();

        Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--role' => $role->name,
            '--permission' => $permission->name,
            '--team' => $team->name,
        ]);

        $this->assertTrue($user->hasRole($role->name));
        $this->assertTrue($user->hasPermission($permission->name));
        $this->assertTrue($user->onTeam($team->name));
    }

    public function test_assigns_with_audit_enabled(): void
    {
        Config::set('gatekeeper.features.audit', true);

        $actor = User::factory()->create();
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
            '--permission' => $permission->name,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue($user->hasPermission($permission->name));
    }

    public function test_fails_if_audit_enabled_and_action_by_missing(): void
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--permission' => 'some-permission',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('You must provide --action_by_model_id', Artisan::output());
    }

    public function test_fails_if_action_by_model_class_does_not_exist(): void
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--action_by_model_id' => 123,
            '--action_by_model_class' => 'Fake\\Actor',
            '--permission' => 'some-permission',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Actor model class [Fake\\Actor] does not exist', Artisan::output());
    }

    public function test_assigns_multiple_roles_permissions_and_teams(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $permissions = Permission::factory()->count(2)->create();
        $teams = Team::factory()->count(2)->create();

        $roleNames = $roles->pluck('name')->implode(',');
        $permissionNames = $permissions->pluck('name')->implode(',');
        $teamNames = $teams->pluck('name')->implode(',');

        Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--role' => $roleNames,
            '--permission' => $permissionNames,
            '--team' => $teamNames,
        ]);

        $this->assertTrue($user->hasAllRoles($roles->pluck('name')));
        $this->assertTrue($user->hasAllPermissions($permissions->pluck('name')));
        $this->assertTrue($user->onAllTeams($teams->pluck('name')));
    }

    public function test_fails_if_model_class_does_not_exist(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => 1,
            '--action_to_model_class' => 'Fake\\Class',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('does not exist', Artisan::output());
    }

    public function test_fails_if_model_is_not_found(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => 999,
            '--action_to_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('not found', Artisan::output());
    }

    public function test_fails_if_nothing_to_assign(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:assign', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Please provide at least one of', Artisan::output());
    }
}
