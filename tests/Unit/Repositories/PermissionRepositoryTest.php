<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Permission\PermissionNotFoundException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PermissionRepositoryTest extends TestCase
{
    protected PermissionRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(PermissionRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(PermissionRepository::class);
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
            ->willReturn(collect([$permission->name => $permission]));

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
            ->willReturn(collect([$permission->name => $permission]));

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

    public function test_update_permission_name_updates_name_and_clears_cache()
    {
        $permission = Permission::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedPermission = $this->repository->updateName($permission, $newName);

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
            ->with(Permission::all()->mapWithKeys(fn (Permission $p) => [$p->name => $p]));

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_get_assigned_permissions_for_model_caches_result()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $this->cacheService->expects($this->once())
            ->method('getModelPermissionLinks')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelPermissionLinks')
            ->with($user, collect([[
                'name' => $permission->name,
                'denied' => 0,
            ]]));

        $permissions = $this->repository->assignedToModel($user);

        $this->assertEquals($permission->name, $permissions->first()->name);
    }
}
