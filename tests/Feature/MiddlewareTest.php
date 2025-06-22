<?php

namespace Braxey\Gatekeeper\Tests\Feature;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class MiddlewareTest extends TestCase
{
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

    protected function registerTestRoute(string $middleware): string
    {
        $uri = '/gatekeeper-test';
        Route::middleware($middleware)->get($uri, fn () => 'OK');

        return $uri;
    }
}
