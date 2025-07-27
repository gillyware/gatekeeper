<?php

namespace Gillyware\Gatekeeper\Tests\Feature;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class BladeDirectivesTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->be($this->user);
    }

    public function test_has_permission_directives()
    {
        $permissionName1 = fake()->unique()->word();
        $permissionName2 = fake()->unique()->word();

        Permission::factory()->withName($permissionName1)->create();
        Permission::factory()->withName($permissionName2)->create();

        $this->user->assignPermission($permissionName1);

        $this->assertEquals('YES', $this->renderBladeString("@hasPermission('$permissionName1') YES @endhasPermission"));
        $this->assertEquals('YES', $this->renderBladeString("@hasPermission(\$user, '$permissionName1') YES @endhasPermission", ['user' => $this->user]));
        $this->assertEquals('YES', $this->renderBladeString("@hasAnyPermission(['$permissionName1', '$permissionName2']) YES @endhasAnyPermission"));
        $this->assertEquals('YES', $this->renderBladeString("@hasAllPermissions(['$permissionName1']) YES @endhasAllPermissions"));
        $this->assertEmpty($this->renderBladeString("@hasAllPermissions(collect(['$permissionName1', '$permissionName2'])) YES @endhasAllPermissions"));
    }

    public function test_has_role_directives()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $roleName1 = fake()->unique()->word();
        $roleName2 = fake()->unique()->word();

        Role::factory()->withName($roleName1)->create();
        Role::factory()->withName($roleName2)->create();

        $this->user->assignRole($roleName1);

        $this->assertEquals('YES', $this->renderBladeString("@hasRole('$roleName1') YES @endhasRole"));
        $this->assertEquals('YES', $this->renderBladeString("@hasRole(\$user, '$roleName1') YES @endhasRole", ['user' => $this->user]));
        $this->assertEquals('YES', $this->renderBladeString("@hasAnyRole(['$roleName1', '$roleName2']) YES @endhasAnyRole"));
        $this->assertEquals('YES', $this->renderBladeString("@hasAllRoles(['$roleName1']) YES @endhasAllRoles"));
        $this->assertEmpty($this->renderBladeString("@hasAllRoles(['$roleName1', '$roleName2']) YES @endhasAllRoles"));
    }

    public function test_has_feature_directives()
    {
        Config::set('gatekeeper.features.features.enabled', true);

        $featureName1 = fake()->unique()->word();
        $featureName2 = fake()->unique()->word();

        Feature::factory()->withName($featureName1)->create();
        Feature::factory()->withName($featureName2)->create();

        $this->user->turnFeatureOn($featureName1);

        $this->assertEquals('YES', $this->renderBladeString("@hasFeature('$featureName1') YES @endhasFeature"));
        $this->assertEquals('YES', $this->renderBladeString("@hasFeature(\$user, '$featureName1') YES @endhasFeature", ['user' => $this->user]));
        $this->assertEquals('YES', $this->renderBladeString("@hasAnyFeature(['$featureName1', '$featureName2']) YES @endhasAnyFeature"));
        $this->assertEquals('YES', $this->renderBladeString("@hasAllFeatures(['$featureName1']) YES @endhasAllFeatures"));
        $this->assertEmpty($this->renderBladeString("@hasAllFeatures(['$featureName1', '$featureName2']) YES @endhasAllFeatures"));
    }

    public function test_on_team_directives()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $teamName1 = fake()->unique()->word();
        $teamName2 = fake()->unique()->word();

        $team1 = Team::factory()->withName($teamName1)->create();
        Team::factory()->withName($teamName2)->create();

        $this->user->teams()->attach($team1);

        $this->assertEquals('YES', $this->renderBladeString("@onTeam('$teamName1') YES @endonTeam"));
        $this->assertEquals('YES', $this->renderBladeString("@onTeam(\$user, '$teamName1') YES @endonTeam", ['user' => $this->user]));
        $this->assertEquals('YES', $this->renderBladeString("@onAnyTeam(['$teamName1', '$teamName2']) YES @endonAnyTeam"));
        $this->assertEquals('YES', $this->renderBladeString("@onAllTeams(['$teamName1']) YES @endonAllTeams"));
        $this->assertEmpty($this->renderBladeString("@onAllTeams(collect(['$teamName1', '$teamName2'])) YES @endonAllTeams"));
    }
}
