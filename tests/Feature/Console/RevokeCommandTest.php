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

        Config::set('gatekeeper.features.audit', false);
        Config::set('gatekeeper.features.roles', true);
        Config::set('gatekeeper.features.teams', true);
    }

    public function test_revokes_single_role_permission_and_team(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();

        $user->assignPermission($permission->name);
        $user->assignRole($role->name);
        $user->addToTeam($team->name);

        Config::set('gatekeeper.features.audit', true);

        Artisan::call('gatekeeper:revoke', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--role' => $role->name,
            '--permission' => $permission->name,
            '--team' => $team->name,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertFalse($user->hasPermission($permission));
        $this->assertFalse($user->hasRole($role));
        $this->assertFalse($user->onTeam($team));
    }

    public function test_revokes_multiple_roles_permissions_and_teams(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $permissions = Permission::factory()->count(2)->create();
        $teams = Team::factory()->count(2)->create();

        $user->assignPermissions($permissions);
        $user->assignRoles($roles);
        $user->addToTeams($teams);

        Config::set('gatekeeper.features.audit', true);

        $roleNames = $roles->pluck('name')->implode(',');
        $permissionNames = $permissions->pluck('name')->implode(',');
        $teamNames = $teams->pluck('name')->implode(',');

        Artisan::call('gatekeeper:revoke', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--role' => $roleNames,
            '--permission' => $permissionNames,
            '--team' => $teamNames,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertFalse($user->hasAnyPermission($permissions));
        $this->assertFalse($user->hasAnyRole($roles));
        $this->assertFalse($user->onAnyTeam($teams));
    }

    public function test_fails_if_model_class_does_not_exist()
    {
        Config::set('gatekeeper.features.audit', true);

        $actor = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--model_id' => 1,
            '--model_class' => 'Fake\\Class',
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fails_if_model_is_not_found()
    {
        Config::set('gatekeeper.features.audit', true);

        $actor = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--model_id' => 999,
            '--model_class' => User::class,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fails_if_nothing_to_revoke()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $actor = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fails_if_audit_enabled_but_no_actor()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $exitCode = Artisan::call('gatekeeper:revoke', [
            '--model_id' => $user->id,
            '--model_class' => User::class,
            '--role' => $role->name,
        ]);

        $this->assertEquals(1, $exitCode);
    }
}
