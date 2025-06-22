<?php

namespace Gillyware\Gatekeeper\Tests\Feature;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit', false);
        Config::set('gatekeeper.features.roles', true);
        Config::set('gatekeeper.features.teams', true);
    }

    public function test_has_permission_middleware_allows_access()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();

        Permission::factory()->withName($permissionName)->create();
        $user->assignPermission($permissionName);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_permission:$permissionName"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_has_permission_middleware_denies_access()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();

        Permission::factory()->withName($permissionName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_permission:$permissionName"))
            ->assertForbidden();
    }

    public function test_has_any_permission_middleware_allows_access()
    {
        $user = User::factory()->create();
        [$p1, $p2] = [fake()->unique()->word(), fake()->unique()->word()];

        Permission::factory()->withName($p1)->create();
        Permission::factory()->withName($p2)->create();
        $user->assignPermission($p2);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_permission:$p1,$p2"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_has_any_permission_middleware_denies_access()
    {
        $user = User::factory()->create();
        [$p1, $p2] = [fake()->unique()->word(), fake()->unique()->word()];

        Permission::factory()->withName($p1)->create();
        Permission::factory()->withName($p2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_permission:$p1,$p2"))
            ->assertForbidden();
    }

    public function test_has_role_middleware_allows_access()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();

        Role::factory()->withName($roleName)->create();
        $user->assignRole($roleName);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_role:$roleName"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_has_role_middleware_denies_access()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();

        Role::factory()->withName($roleName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_role:$roleName"))
            ->assertForbidden();
    }

    public function test_has_any_role_middleware_allows_access()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Role::factory()->withName($r1)->create();
        Role::factory()->withName($r2)->create();
        $user->assignRole($r1);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_role:$r1,$r2"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_has_any_role_middleware_denies_access()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Role::factory()->withName($r1)->create();
        Role::factory()->withName($r2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_role:$r1,$r2"))
            ->assertForbidden();
    }

    public function test_on_team_middleware_allows_access()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();

        $team = Team::factory()->withName($teamName)->create();
        $user->teams()->attach($team);

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_team:$teamName"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_on_team_middleware_denies_access()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();

        Team::factory()->withName($teamName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_team:$teamName"))
            ->assertForbidden();
    }

    public function test_on_any_team_middleware_allows_access()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        [$t1, $t2] = [fake()->unique()->word(), fake()->unique()->word()];

        Team::factory()->withName($t1)->create();
        Team::factory()->withName($t2)->create();
        $user->addToTeam($t2);

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_any_team:$t1,$t2"))
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_on_any_team_middleware_denies_access()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        [$t1, $t2] = [fake()->unique()->word(), fake()->unique()->word()];

        Team::factory()->withName($t1)->create();
        Team::factory()->withName($t2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_any_team:$t1,$t2"))
            ->assertForbidden();
    }

    protected function registerTestRoute(string $middleware): string
    {
        $uri = '/gatekeeper-test';
        Route::middleware($middleware)->get($uri, fn () => 'OK');

        return $uri;
    }
}
