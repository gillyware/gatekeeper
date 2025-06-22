<?php

namespace Gillyware\Gatekeeper\Tests\Feature;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class BladeDirectivesTest extends TestCase
{
    public function test_has_permission_directives()
    {
        $user = User::factory()->create();
        $this->be($user);

        $permissionName1 = fake()->unique()->word();
        $permissionName2 = fake()->unique()->word();

        Permission::factory()->withName($permissionName1)->create();
        Permission::factory()->withName($permissionName2)->create();

        $user->assignPermission($permissionName1);

        $this->assertEquals('YES', $this->renderBladeString("@hasPermission('$permissionName1') YES @endhasPermission"));
        $this->assertEquals('YES', $this->renderBladeString("@hasPermission(\$user, '$permissionName1') YES @endhasPermission", compact('user')));
        $this->assertEquals('YES', $this->renderBladeString("@hasAnyPermission(['$permissionName1', '$permissionName2']) YES @endhasAnyPermission"));
        $this->assertEquals('YES', $this->renderBladeString("@hasAllPermissions(['$permissionName1']) YES @endhasAllPermissions"));
        $this->assertEmpty($this->renderBladeString("@hasAllPermissions(collect(['$permissionName1', '$permissionName2'])) YES @endhasAllPermissions"));
    }

    public function test_has_role_directives()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $this->be($user);

        $roleName1 = fake()->unique()->word();
        $roleName2 = fake()->unique()->word();

        Role::factory()->withName($roleName1)->create();
        Role::factory()->withName($roleName2)->create();

        $user->assignRole($roleName1);

        $this->assertEquals('YES', $this->renderBladeString("@hasRole('$roleName1') YES @endhasRole"));
        $this->assertEquals('YES', $this->renderBladeString("@hasRole(\$user, '$roleName1') YES @endhasRole", compact('user')));
        $this->assertEquals('YES', $this->renderBladeString("@hasAnyRole(['$roleName1', '$roleName2']) YES @endhasAnyRole"));
        $this->assertEquals('YES', $this->renderBladeString("@hasAllRoles(['$roleName1']) YES @endhasAllRoles"));
        $this->assertEmpty($this->renderBladeString("@hasAllRoles(['$roleName1', '$roleName2']) YES @endhasAllRoles"));
    }

    public function test_on_team_directives()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $this->be($user);

        $teamName1 = fake()->unique()->word();
        $teamName2 = fake()->unique()->word();

        $team1 = Team::factory()->withName($teamName1)->create();
        Team::factory()->withName($teamName2)->create();

        $user->teams()->attach($team1);

        $this->assertEquals('YES', $this->renderBladeString("@onTeam('$teamName1') YES @endonTeam"));
        $this->assertEquals('YES', $this->renderBladeString("@onTeam(\$user, '$teamName1') YES @endonTeam", compact('user')));
        $this->assertEquals('YES', $this->renderBladeString("@onAnyTeam(['$teamName1', '$teamName2']) YES @endonAnyTeam"));
        $this->assertEquals('YES', $this->renderBladeString("@onAllTeams(['$teamName1']) YES @endonAllTeams"));
        $this->assertEmpty($this->renderBladeString("@onAllTeams(collect(['$teamName1', '$teamName2'])) YES @endonAllTeams"));
    }

    public function test_directives_fail_gracefully_with_no_user()
    {
        $this->assertEmpty($this->renderBladeString("@hasRole('admin') YES @endhasRole"));
        $this->assertEmpty($this->renderBladeString("@hasPermission('edit') YES @endhasPermission"));
        $this->assertEmpty($this->renderBladeString("@onTeam('devs') YES @endonTeam"));
        $this->assertEmpty($this->renderBladeString("@hasAnyRole(['admin']) YES @endhasAnyRole"));
        $this->assertEmpty($this->renderBladeString("@hasAllPermissions(['edit']) YES @endhasAllPermissions"));
        $this->assertEmpty($this->renderBladeString("@onAnyTeam(['devs']) YES @endonAnyTeam"));
        $this->assertEmpty($this->renderBladeString("@onAllTeams(['devs']) YES @endonAllTeams"));
    }

    public function test_directives_fail_gracefully_if_method_missing()
    {
        $user = new class
        {
            public function __get($key)
            {
                return null;
            }
        };

        $this->assertEmpty($this->renderBladeString("@hasRole(\$user, 'admin') YES @endhasRole", compact('user')));
        $this->assertEmpty($this->renderBladeString("@hasPermission(\$user, 'edit') YES @endhasPermission", compact('user')));
        $this->assertEmpty($this->renderBladeString("@onTeam(\$user, 'devs') YES @endonTeam", compact('user')));
        $this->assertEmpty($this->renderBladeString("@hasAnyPermission(\$user, ['edit']) YES @endhasAnyPermission", compact('user')));
        $this->assertEmpty($this->renderBladeString("@hasAllRoles(\$user, ['admin']) YES @endhasAllRoles", compact('user')));
        $this->assertEmpty($this->renderBladeString("@onAnyTeam(\$user, ['devs']) YES @endonAnyTeam", compact('user')));
        $this->assertEmpty($this->renderBladeString("@onAllTeams(\$user, ['devs']) YES @endonAllTeams", compact('user')));
    }
}
