<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\GatekeeperService;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class GatekeeperServiceTest extends TestCase
{
    protected GatekeeperService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GatekeeperService;
    }

    public function test_we_can_create_a_permission()
    {
        $permissionName = fake()->unique()->word();

        $permission = $this->service->createPermission($permissionName);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($permissionName, $permission->name);
    }

    public function test_we_can_create_a_role_when_roles_feature_is_enabled()
    {
        Config::set('gatekeeper.features.roles', true);

        $roleName = fake()->unique()->word();

        $role = $this->service->createRole($roleName);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($roleName, $role->name);
    }

    public function test_it_throws_exception_when_creating_role_if_roles_feature_is_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $roleName = fake()->unique()->word();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Roles feature is disabled.');

        $this->service->createRole($roleName);
    }

    public function test_we_can_create_a_team_when_teams_feature_is_enabled()
    {
        Config::set('gatekeeper.features.teams', true);

        $teamName = fake()->unique()->word();

        $team = $this->service->createTeam($teamName);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals($teamName, $team->name);
    }

    public function test_it_throws_exception_when_creating_team_if_teams_feature_is_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $teamName = fake()->unique()->word();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Teams feature is disabled.');

        $this->service->createTeam($teamName);
    }
}
