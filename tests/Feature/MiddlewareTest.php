<?php

namespace Gillyware\Gatekeeper\Tests\Feature;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit.enabled', false);
        Config::set('gatekeeper.features.roles.enabled', true);
        Config::set('gatekeeper.features.features.enabled', true);
        Config::set('gatekeeper.features.teams.enabled', true);
    }

    public function test_has_permission_middleware_allows_access()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();

        Permission::factory()->withName($permissionName)->create();
        $user->assignPermission($permissionName);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_permission:$permissionName"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_has_permission_middleware_denies_access()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();

        Permission::factory()->withName($permissionName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_permission:$permissionName"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
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
            ->assertStatus(Response::HTTP_OK)
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
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_has_role_middleware_allows_access()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();

        Role::factory()->withName($roleName)->create();
        $user->assignRole($roleName);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_role:$roleName"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_has_role_middleware_denies_access()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();

        Role::factory()->withName($roleName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_role:$roleName"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_has_any_role_middleware_allows_access()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Role::factory()->withName($r1)->create();
        Role::factory()->withName($r2)->create();
        $user->assignRole($r1);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_role:$r1,$r2"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_has_any_role_middleware_denies_access()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Role::factory()->withName($r1)->create();
        Role::factory()->withName($r2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_role:$r1,$r2"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_has_feature_middleware_allows_access()
    {
        Config::set('gatekeeper.features.features.enabled', true);

        $user = User::factory()->create();
        $featureName = fake()->unique()->word();

        Feature::factory()->withName($featureName)->create();
        $user->turnFeatureOn($featureName);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_feature:$featureName"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_has_feature_middleware_denies_access()
    {
        Config::set('gatekeeper.features.features.enabled', true);

        $user = User::factory()->create();
        $featureName = fake()->unique()->word();

        Feature::factory()->withName($featureName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_feature:$featureName"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_has_any_feature_middleware_allows_access()
    {
        Config::set('gatekeeper.features.features.enabled', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Feature::factory()->withName($r1)->create();
        Feature::factory()->withName($r2)->create();
        $user->turnFeatureOn($r1);

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_feature:$r1,$r2"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_has_any_feature_middleware_denies_access()
    {
        Config::set('gatekeeper.features.features.enabled', true);

        $user = User::factory()->create();
        [$r1, $r2] = [fake()->unique()->word(), fake()->unique()->word()];

        Feature::factory()->withName($r1)->create();
        Feature::factory()->withName($r2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("has_any_feature:$r1,$r2"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_on_team_middleware_allows_access()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();

        $team = Team::factory()->withName($teamName)->create();
        $user->teams()->attach($team);

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_team:$teamName"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_on_team_middleware_denies_access()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();

        Team::factory()->withName($teamName)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_team:$teamName"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_on_any_team_middleware_allows_access()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        [$t1, $t2] = [fake()->unique()->word(), fake()->unique()->word()];

        Team::factory()->withName($t1)->create();
        Team::factory()->withName($t2)->create();
        $user->addToTeam($t2);

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_any_team:$t1,$t2"))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('OK');
    }

    public function test_on_any_team_middleware_denies_access()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        [$t1, $t2] = [fake()->unique()->word(), fake()->unique()->word()];

        Team::factory()->withName($t1)->create();
        Team::factory()->withName($t2)->create();

        $this->actingAs($user)
            ->get($this->registerTestRoute("on_any_team:$t1,$t2"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    protected function registerTestRoute(string $middleware): string
    {
        $uri = '/gatekeeper-test';
        Route::middleware($middleware)->get($uri, fn () => 'OK');

        return $uri;
    }
}
