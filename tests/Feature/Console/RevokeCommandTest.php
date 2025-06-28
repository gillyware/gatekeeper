<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class RevokeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles', true);
        Config::set('gatekeeper.features.teams', true);
    }

    public function test_revokes_single_role_permission_and_team(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();

        $user->assignRole($role);
        $user->assignPermission($permission);
        $user->teams()->attach($team);

        Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--role' => $role->name,
            '--permission' => $permission->name,
            '--team' => $team->name,
        ]);

        $this->assertFalse($user->hasRole($role->name));
        $this->assertFalse($user->hasPermission($permission->name));
        $this->assertFalse($user->onTeam($team->name));
    }

    public function test_revokes_with_audit_enabled_and_explicit_actor(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $actor = User::factory()->create();
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $user->assignPermission($permission);

        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
            '--permission' => $permission->name,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertFalse($user->hasPermission($permission->name));
    }

    public function test_revokes_with_audit_enabled_and_no_actor_defaults_to_system(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $user->assignPermission($permission);

        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--permission' => $permission->name,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertFalse($user->hasPermission($permission->name));
    }

    public function test_fails_if_action_by_model_class_does_not_exist(): void
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--action_by_model_id' => 123,
            '--action_by_model_class' => 'Fake\\Actor',
            '--permission' => 'some-permission',
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_revokes_multiple_roles_permissions_and_teams(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $permissions = Permission::factory()->count(2)->create();
        $teams = Team::factory()->count(2)->create();

        $user->assignRoles($roles);
        $user->assignPermissions($permissions);
        $user->teams()->attach($teams);

        Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
            '--role' => $roles->pluck('name')->implode(','),
            '--permission' => $permissions->pluck('name')->implode(','),
            '--team' => $teams->pluck('name')->implode(','),
        ]);

        $this->assertFalse($user->hasAnyRole($roles->pluck('name')));
        $this->assertFalse($user->hasAnyPermission($permissions->pluck('name')));
        $this->assertFalse($user->onAnyTeam($teams->pluck('name')));
    }

    public function test_fails_if_model_class_does_not_exist(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => 1,
            '--action_to_model_class' => 'Fake\\Class',
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fails_if_model_is_not_found(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => 999,
            '--action_to_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fails_if_nothing_to_revoke(): void
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--action_to_model_id' => $user->id,
            '--action_to_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
    }
}
