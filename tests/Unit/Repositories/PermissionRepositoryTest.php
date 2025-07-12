<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Permission\PermissionNotFoundException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class PermissionRepositoryTest extends TestCase
{
    protected PermissionRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit.enabled', false);

        $cacheMock = $this->createMock(CacheService::class);
        $this->cacheService = $cacheMock;

        $this->repository = new PermissionRepository($cacheMock);
    }

    public function test_permission_exists_returns_true_if_exists()
    {
        $permission = Permission::factory()->create();

        $this->assertTrue($this->repository->exists($permission->name));
    }

    public function test_permission_exists_returns_false_if_not_exists()
    {
        $this->assertFalse($this->repository->exists(fake()->unique()->word()));
    }

    public function test_find_by_name_returns_permission_if_exists()
    {
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect([$permission]));

        $result = $this->repository->findByName($permission->name);

        $this->assertTrue($permission->is($result));
    }

    public function test_find_by_name_returns_null_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect());

        $result = $this->repository->findByName(fake()->unique()->word());

        $this->assertNull($result);
    }

    public function test_find_or_fail_by_name_returns_permission_if_exists()
    {
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect([$permission]));

        $result = $this->repository->findOrFailByName($permission->name);

        $this->assertTrue($permission->is($result));
    }

    public function test_find_or_fail_by_name_throws_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect());

        $this->expectException(PermissionNotFoundException::class);

        $this->repository->findOrFailByName(fake()->unique()->word());
    }

    public function test_create_stores_permission_and_forgets_cache()
    {
        $this->cacheService->expects($this->once())->method('invalidateCacheForAllPermissions');

        $name = fake()->unique()->word();
        $permission = $this->repository->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertTrue($this->repository->exists($name));
    }

    public function test_update_permission_updates_name_and_clears_cache()
    {
        $permission = Permission::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedPermission = $this->repository->update($permission, $newName);

        $this->assertEquals($newName, $updatedPermission->name);
    }

    public function test_deactivate_permission_sets_active_to_false_and_clears_cache()
    {
        $permission = Permission::factory()->create(['is_active' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $deactivatedPermission = $this->repository->deactivate($permission);

        $this->assertFalse($deactivatedPermission->is_active);
    }

    public function test_reactivate_permission_sets_active_to_true_and_clears_cache()
    {
        $permission = Permission::factory()->inactive()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $activatedPermission = $this->repository->reactivate($permission);

        $this->assertTrue($activatedPermission->is_active);
    }

    public function test_delete_permission_soft_deletes_and_clears_cache()
    {
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $this->repository->delete($permission);

        $this->assertSoftDeleted($permission->fresh());
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Permission::factory()->count(2)->make();

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn($cached);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(
            $cached->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_all_caches_result_if_not_cached()
    {
        $permissions = Permission::factory()->count(3)->create();

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putAllPermissions')
            ->with(Permission::all()->values());

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_active_returns_only_active_permissions()
    {
        $inactive = Permission::factory()->count(2)->inactive()->create();
        $active = Permission::factory()->count(2)->create();

        $all = $inactive->concat($active);

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn($all);

        $result = $this->repository->active();

        $this->assertEqualsCanonicalizing(
            $active->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_permissions()
    {
        $permissions = Permission::factory()->count(3)->create();
        $names = $permissions->pluck('name');

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn($permissions);

        $result = $this->repository->whereNameIn($names);

        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_empty_collection_when_no_matches()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect());

        $result = $this->repository->whereNameIn(['nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_get_all_permission_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelPermissionNames')
            ->with($user, collect([$permission->name]));

        $names = $this->repository->namesForModel($user);

        $this->assertContains($permission->name, $names->toArray());
    }

    public function test_get_all_permissions_for_model_returns_permissions()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(collect([$permission->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect([$permission]));

        $permissions = $this->repository->forModel($user);

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->is($permission));
    }

    public function test_get_all_permissions_for_model_returns_empty_when_no_permissions()
    {
        $user = User::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect());

        $permissions = $this->repository->forModel($user);

        $this->assertCount(0, $permissions);
    }

    public function test_active_for_model_returns_active_permissions()
    {
        $user = User::factory()->create();
        $activePermission = Permission::factory()->create();
        $inactivePermission = Permission::factory()->inactive()->create();

        $user->permissions()->attach([$activePermission->id, $inactivePermission->id]);

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(collect([$activePermission->name, $inactivePermission->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect([$activePermission, $inactivePermission]));

        $permissions = $this->repository->activeForModel($user);

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->is($activePermission));
    }

    public function test_find_by_name_for_model_returns_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(collect([$permission->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect([$permission]));

        $result = $this->repository->findByNameForModel($user, $permission->name);
        $this->assertTrue($result->is($permission));
    }

    public function test_find_by_name_for_model_returns_null_if_not_found()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllPermissions')
            ->willReturn(collect());

        $result = $this->repository->findByNameForModel($user, $permissionName);
        $this->assertNull($result);
    }
}
