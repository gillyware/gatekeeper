<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\GatekeeperService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class GatekeeperServiceTest extends TestCase
{
    protected GatekeeperService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app('gatekeeper');
        $this->user = User::factory()->create();
        $this->service->setActor($this->user);
    }

    public function test_create_permission_delegates_to_permission_service()
    {
        $permissionName = fake()->unique()->word();

        $permission = $this->service->createPermission($permissionName);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($permissionName, $permission->name);
    }

    public function test_update_permission_delegates_to_permission_service()
    {
        $permission = Permission::factory()->create();
        $newName = fake()->unique()->word();

        $updatedPermission = $this->service->updatePermission($permission, $newName);

        $this->assertInstanceOf(Permission::class, $updatedPermission);
        $this->assertEquals($newName, $updatedPermission->name);
    }

    public function test_deactivate_permission_delegates_to_permission_service()
    {
        $permission = Permission::factory()->create();

        $permission = $this->service->deactivatePermission($permission);

        $this->assertFalse($permission->fresh()->is_active);
    }

    public function test_reactivate_permission_delegates_to_permission_service()
    {
        $permission = Permission::factory()->inactive()->create();

        $permission = $this->service->reactivatePermission($permission);

        $this->assertTrue($permission->fresh()->is_active);
    }

    public function test_delete_permission_delegates_to_permission_service()
    {
        $permission = Permission::factory()->create();

        $deleted = $this->service->deletePermission($permission);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($permission);
    }

    public function test_create_role_delegates_to_role_service()
    {
        Config::set('gatekeeper.features.roles', true);

        $roleName = fake()->unique()->word();

        $role = $this->service->createRole($roleName);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($roleName, $role->name);
    }

    public function test_update_role_delegates_to_role_service()
    {
        Config::set('gatekeeper.features.roles', true);

        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $updatedRole = $this->service->updateRole($role, $newName);

        $this->assertInstanceOf(Role::class, $updatedRole);
        $this->assertEquals($newName, $updatedRole->name);
    }

    public function test_deactivate_role_delegates_to_role_service()
    {
        Config::set('gatekeeper.features.roles', true);

        $role = Role::factory()->create();

        $role = $this->service->deactivateRole($role);

        $this->assertFalse($role->fresh()->is_active);
    }

    public function test_reactivate_role_delegates_to_role_service()
    {
        Config::set('gatekeeper.features.roles', true);

        $role = Role::factory()->inactive()->create();

        $role = $this->service->reactivateRole($role);

        $this->assertTrue($role->fresh()->is_active);
    }

    public function test_delete_role_delegates_to_role_service()
    {
        Config::set('gatekeeper.features.roles', true);

        $role = Role::factory()->create();

        $deleted = $this->service->deleteRole($role);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($role);
    }

    public function test_create_team_delegates_to_team_service()
    {
        Config::set('gatekeeper.features.teams', true);

        $teamName = fake()->unique()->word();

        $team = $this->service->createTeam($teamName);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals($teamName, $team->name);
    }

    public function test_update_team_delegates_to_team_service()
    {
        Config::set('gatekeeper.features.teams', true);

        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $updatedTeam = $this->service->updateTeam($team, $newName);

        $this->assertInstanceOf(Team::class, $updatedTeam);
        $this->assertEquals($newName, $updatedTeam->name);
    }

    public function test_deactivate_team_delegates_to_team_service()
    {
        Config::set('gatekeeper.features.teams', true);

        $team = Team::factory()->create();

        $team = $this->service->deactivateTeam($team);

        $this->assertFalse($team->fresh()->is_active);
    }

    public function test_reactivate_team_delegates_to_team_service()
    {
        Config::set('gatekeeper.features.teams', true);

        $team = Team::factory()->inactive()->create();

        $team = $this->service->reactivateTeam($team);

        $this->assertTrue($team->fresh()->is_active);
    }

    public function test_delete_team_delegates_to_team_service()
    {
        Config::set('gatekeeper.features.teams', true);

        $team = Team::factory()->create();

        $deleted = $this->service->deleteTeam($team);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($team);
    }

    public function test_model_permission_methods_delegate()
    {
        $user = User::factory()->create();
        $perm1 = Permission::factory()->create(['name' => fake()->unique()->word()]);
        $perm2 = Permission::factory()->create(['name' => fake()->unique()->word()]);

        $this->assertTrue($this->service->assignPermissionToModel($user, $perm1->name));
        $this->assertTrue($this->service->assignPermissionsToModel($user, [$perm2->name]));
        $this->assertTrue($this->service->modelHasPermission($user, $perm1->name));
        $this->assertTrue($this->service->modelHasAnyPermission($user, [$perm1->name, $perm2->name]));
        $this->assertTrue($this->service->modelHasAllPermissions($user, [$perm1->name, $perm2->name]));
        $this->assertTrue($this->service->revokePermissionFromModel($user, $perm1->name));
        $this->assertTrue($this->service->revokePermissionsFromModel($user, [$perm2->name]));
    }

    public function test_model_role_methods_delegate()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => fake()->unique()->word()]);
        $role2 = Role::factory()->create(['name' => fake()->unique()->word()]);

        $this->assertTrue($this->service->assignRoleToModel($user, $role1->name));
        $this->assertTrue($this->service->assignRolesToModel($user, [$role2->name]));
        $this->assertTrue($this->service->modelHasRole($user, $role1->name));
        $this->assertTrue($this->service->modelHasAnyRole($user, [$role1->name, $role2->name]));
        $this->assertTrue($this->service->modelHasAllRoles($user, [$role1->name, $role2->name]));
        $this->assertTrue($this->service->revokeRoleFromModel($user, $role1->name));
        $this->assertTrue($this->service->revokeRolesFromModel($user, [$role2->name]));
    }

    public function test_model_team_methods_delegate()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $team1 = Team::factory()->create(['name' => fake()->unique()->word()]);
        $team2 = Team::factory()->create(['name' => fake()->unique()->word()]);

        $this->assertTrue($this->service->addModelToTeam($user, $team1->name));
        $this->assertTrue($this->service->addModelToTeams($user, [$team2->name]));
        $this->assertTrue($this->service->modelOnTeam($user, $team1->name));
        $this->assertTrue($this->service->modelOnAnyTeam($user, [$team1->name, $team2->name]));
        $this->assertTrue($this->service->modelOnAllTeams($user, [$team1->name, $team2->name]));
        $this->assertTrue($this->service->removeModelFromTeam($user, $team1->name));
        $this->assertTrue($this->service->removeModelFromTeams($user, [$team2->name]));
    }
}
