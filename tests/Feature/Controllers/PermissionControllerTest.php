<?php

namespace Gillyware\Gatekeeper\Tests\Permission\Controllers;

use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PermissionControllerTest extends TestCase
{
    private User $user;

    private CacheRepository $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);

        $this->cacheRepository = app()->make(CacheRepository::class);
    }

    public function test_index_returns_paginated_permissions()
    {
        Permission::factory()->count(15)->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $this->getJson(route('gatekeeper.api.permissions.index', [
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

    public function test_show_returns_a_permission()
    {
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $this->getJson(route('gatekeeper.api.permissions.show', $permission->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['id' => $permission->id]);
    }

    public function test_store_creates_permission()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $response = $this->postJson(route('gatekeeper.api.permissions.store'), ['name' => 'example.permission']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull(Permission::firstWhere('name', 'example.permission'));
    }

    public function test_store_fails_with_duplicate()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        Permission::factory()->withName('duplicate.permission')->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.permissions.store'), ['name' => 'duplicate.permission'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_update_permission_name()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->create(['name' => 'old.name']);
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), [
            'action' => EntityUpdateAction::Name->value,
            'value' => 'new.name',
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['name' => 'new.name']);
    }

    public function test_grant_permission_by_default()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), [
            'action' => EntityUpdateAction::DefaultGrant->value,
            'value' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['grant_by_default' => true]);
    }

    public function test_revoke_permission_default_grant()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->grantByDefault()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), [
            'action' => EntityUpdateAction::DefaultGrant->value,
            'value' => false,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['grant_by_default' => false]);
    }

    public function test_deactivate_permission()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), [
            'action' => EntityUpdateAction::Status->value,
            'value' => false,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => false]);
    }

    public function test_reactivate_permission()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->inactive()->create();
        $this->cacheRepository->clear();

        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), [
            'action' => EntityUpdateAction::Status->value,
            'value' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['is_active' => true]);
    }

    public function test_delete_permission()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();

        $this->deleteJson(route('gatekeeper.api.permissions.delete', ['permission' => $permission]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted(Permission::withTrashed()->find($permission->id));
    }

    public function test_protected_routes_fail_without_permission()
    {
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.permissions.store'), ['name' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->patchJson(route('gatekeeper.api.permissions.update', ['permission' => $permission]), ['action' => EntityUpdateAction::Name->value, 'value' => fake()->word()])->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->deleteJson(route('gatekeeper.api.permissions.delete', ['permission' => $permission]))->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
