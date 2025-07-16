<?php

namespace Gillyware\Gatekeeper\Tests\Feature\Controllers;

use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class TeamControllerTest extends TestCase
{
    private User $user;

    private CacheRepository $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.teams.enabled', true);

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);

        $this->cacheRepository = app()->make(CacheRepository::class);
    }

    public function test_index_returns_paginated_teams()
    {
        Team::factory()->count(15)->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);

        $this->getJson(route('gatekeeper.api.teams.index', [
            'page' => 1,
            'search_term' => '',
            'prioritized_attribute' => 'name',
            'name_order' => 'asc',
            'is_active_order' => 'desc',
        ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['current_page', 'data', 'from', 'last_page', 'per_page', 'to', 'total'])
            ->assertJsonCount(10, 'data');
    }

    public function test_show_returns_a_team()
    {
        $team = Team::factory()->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);

        $this->getJson(route('gatekeeper.api.teams.show', $team->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['id' => $team->id]);
    }

    public function test_store_creates_team()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);

        $response = $this->postJson(route('gatekeeper.api.teams.store'), ['name' => 'example.team']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull(Team::firstWhere('name', 'example.team'));
    }

    public function test_store_fails_with_duplicate()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);
        Team::factory()->withName('duplicate.team')->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.teams.store'), ['name' => 'duplicate.team'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_update_team()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);
        $team = Team::factory()->create(['name' => 'old.name']);
        $this->cacheRepository->clear();

        $this->putJson(route('gatekeeper.api.teams.update', ['team' => $team]), ['name' => 'new.name'])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['name' => 'new.name']);
    }

    public function test_deactivate_team()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);
        $team = Team::factory()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.teams.deactivate', ['team' => $team]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => false]);
    }

    public function test_reactivate_team()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);
        $team = Team::factory()->inactive()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.teams.reactivate', ['team' => $team]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => true]);
    }

    public function test_delete_team()
    {
        $this->user->assignAllPermissions([GatekeeperPermissionName::View, GatekeeperPermissionName::Manage]);
        $team = Team::factory()->create();
        $this->cacheRepository->clear();

        $this->deleteJson(route('gatekeeper.api.teams.delete', ['team' => $team]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted(Team::withTrashed()->find($team->id));
    }

    public function test_protected_routes_fail_without_team()
    {
        $team = Team::factory()->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.teams.store'), ['name' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->putJson(route('gatekeeper.api.teams.update', ['team' => $team]), ['name' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->patchJson(route('gatekeeper.api.teams.deactivate', ['team' => $team]))->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->patchJson(route('gatekeeper.api.teams.reactivate', ['team' => $team]))->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->deleteJson(route('gatekeeper.api.teams.delete', ['team' => $team]))->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
