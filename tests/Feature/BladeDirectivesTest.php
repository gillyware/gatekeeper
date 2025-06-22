<?php

namespace Braxey\Gatekeeper\Tests\Feature;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class BladeDirectivesTest extends TestCase
{
    public function test_has_role_directive_with_default_user()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $this->be($user);

        $roleName = 'admin';
        Role::factory()->withName($roleName)->create();
        $user->assignRole($roleName);

        $blade = "@hasRole('$roleName') YES @endhasRole";

        $this->assertEquals('YES', $this->renderBladeString($blade));
    }

    public function test_has_role_directive_with_explicit_user()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $roleName = 'admin';
        Role::factory()->withName($roleName)->create();
        $user->assignRole($roleName);

        $blade = "@hasRole(\$user, '$roleName') YES @endhasRole";

        $this->assertEquals('YES', $this->renderBladeString($blade, compact('user')));
    }

    public function test_has_permission_directive_with_default_user()
    {
        $user = User::factory()->create();
        $this->be($user);

        $permissionName = 'edit-posts';
        Permission::factory()->withName($permissionName)->create();
        $user->assignPermission($permissionName);

        $blade = "@hasPermission('$permissionName') YES @endhasPermission";

        $this->assertEquals('YES', $this->renderBladeString($blade));
    }

    public function test_has_permission_directive_with_explicit_user()
    {
        $user = User::factory()->create();
        $permissionName = 'edit-posts';
        Permission::factory()->withName($permissionName)->create();
        $user->assignPermission($permissionName);

        $blade = "@hasPermission(\$user, '$permissionName') YES @endhasPermission";

        $this->assertEquals('YES', $this->renderBladeString($blade, compact('user')));
    }

    public function test_on_team_directive_with_default_user()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $this->be($user);

        $teamName = 'devs';
        $team = Team::factory()->withName($teamName)->create();
        $user->teams()->attach($team);

        $blade = "@onTeam('$teamName') YES @endonTeam";

        $this->assertEquals('YES', $this->renderBladeString($blade));
    }

    public function test_on_team_directive_with_explicit_user()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $teamName = 'devs';
        $team = Team::factory()->withName($teamName)->create();
        $user->teams()->attach($team);

        $blade = "@onTeam(\$user, '$teamName') YES @endonTeam";

        $this->assertEquals('YES', $this->renderBladeString($blade, compact('user')));
    }

    public function test_directives_fail_gracefully_if_method_does_not_exist()
    {
        $user = new class
        {
            public function __get($name)
            {
                return null;
            }
        };

        $bladeRole = "@hasRole(\$user, 'admin') YES @endhasRole";
        $bladePerm = "@hasPermission(\$user, 'edit') YES @endhasPermission";
        $bladeTeam = "@onTeam(\$user, 'devs') YES @endonTeam";

        $this->assertEquals('', $this->renderBladeString($bladeRole, compact('user')));
        $this->assertEquals('', $this->renderBladeString($bladePerm, compact('user')));
        $this->assertEquals('', $this->renderBladeString($bladeTeam, compact('user')));
    }

    public function test_directives_return_nothing_when_user_is_not_authenticated()
    {
        $blade = "@hasRole('admin') YES @endhasRole";

        $this->assertEquals('', $this->renderBladeString($blade));
    }
}
