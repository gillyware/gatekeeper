<?php

namespace Gillyware\Gatekeeper\Tests\Feature\Controllers;

use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class RoleControllerTest extends TestCase
{
    private User $user;

    private CacheRepository $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles.enabled', true);

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);

        $this->cacheRepository = app()->make(CacheRepository::class);
    }

    public function test_index_returns_paginated_roles()
    {
        Role::factory()->count(15)->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $this->getJson(route('gatekeeper.api.roles.index', [
            'page' => 1,
            'search_term' => '',
            'prioritized_attribute' => 'name',
            'name_order' => 'asc',
            'grant_by_default_order' => 'asc',
            'is_active_order' => 'desc',
        ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['current_page', 'data', 'from', 'last_page', 'per_page', 'to', 'total'])
            ->assertJsonCount(10, 'data');
    }

    public function test_show_returns_a_role()
    {
        $role = Role::factory()->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $this->getJson(route('gatekeeper.api.roles.show', $role->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['id' => $role->id]);
    }

    public function test_store_creates_role()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $response = $this->postJson(route('gatekeeper.api.roles.store'), ['name' => 'example.role']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull(Role::firstWhere('name', 'example.role'));
    }

    public function test_store_fails_with_duplicate()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        Role::factory()->withName('duplicate.role')->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.roles.store'), ['name' => 'duplicate.role'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_update_role_name()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->create(['name' => 'old.name']);
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), [
            'action' => EntityUpdateAction::Name->value,
            'value' => 'new.name',
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['name' => 'new.name']);
    }

    public function test_grant_role_by_default()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), [
            'action' => EntityUpdateAction::DefaultGrant->value,
            'value' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['grant_by_default' => true]);
    }

    public function test_revoke_role_default_grant()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->grantByDefault()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), [
            'action' => EntityUpdateAction::DefaultGrant->value,
            'value' => false,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['grant_by_default' => false]);
    }

    public function test_deactivate_role()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), [
            'action' => EntityUpdateAction::Status->value,
            'value' => false,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => false]);
    }

    public function test_reactivate_role()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->inactive()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), [
            'action' => EntityUpdateAction::Status->value,
            'value' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => true]);
    }

    public function test_delete_role()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $role = Role::factory()->create();
        $this->cacheRepository->clear();

        $this->deleteJson(route('gatekeeper.api.roles.delete', ['role' => $role]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted(Role::withTrashed()->find($role->id));
    }

    public function test_protected_routes_fail_without_role()
    {
        $role = Role::factory()->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.roles.store'), ['name' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->patchJson(route('gatekeeper.api.roles.update', ['role' => $role]), ['action' => EntityUpdateAction::Name->value, 'value' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->deleteJson(route('gatekeeper.api.roles.delete', ['role' => $role]))->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
