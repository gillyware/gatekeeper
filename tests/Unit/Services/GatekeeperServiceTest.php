<?php

namespace Braxey\Gatekeeper\Tests\Unit\Services;

use Braxey\Gatekeeper\Exceptions\RolesFeatureDisabledException;
use Braxey\Gatekeeper\Exceptions\TeamsFeatureDisabledException;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\GatekeeperService;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class GatekeeperServiceTest extends TestCase
{
    protected GatekeeperService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app('gatekeeper');
    }

    public function test_create_permission_delegates_to_permission_service()
    {
        $permissionName = fake()->unique()->word();

        $permission = $this->service->createPermission($permissionName);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($permissionName, $permission->name);
    }

    public function test_create_role_delegates_to_role_service_when_enabled()
    {
        Config::set('gatekeeper.features.roles', true);

        $roleName = fake()->unique()->word();

        $role = $this->service->createRole($roleName);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($roleName, $role->name);
    }

    public function test_create_role_throws_when_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $this->expectException(RolesFeatureDisabledException::class);

        $this->service->createRole(fake()->unique()->word());
    }

    public function test_create_team_delegates_to_team_service_when_enabled()
    {
        Config::set('gatekeeper.features.teams', true);

        $teamName = fake()->unique()->word();

        $team = $this->service->createTeam($teamName);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals($teamName, $team->name);
    }

    public function test_create_team_throws_when_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $this->service->createTeam(fake()->unique()->word());
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
