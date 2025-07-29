<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Role\RoleNotFoundException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RoleRepositoryTest extends TestCase
{
    protected RoleRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(RoleRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(RoleRepository::class);
    }

    public function test_role_exists_returns_true_if_exists()
    {
        $role = Role::factory()->create();

        $this->assertTrue($this->repository->exists($role->name));
    }

    public function test_role_exists_returns_false_if_not_exists()
    {
        $this->assertFalse($this->repository->exists(fake()->unique()->word()));
    }

    public function test_find_by_name_returns_role_if_exists()
    {
        $role = Role::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect([$role->name => $role]));

        $result = $this->repository->findByName($role->name);

        $this->assertTrue($role->is($result));
    }

    public function test_find_by_name_returns_null_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect());

        $result = $this->repository->findByName(fake()->unique()->word());

        $this->assertNull($result);
    }

    public function test_find_or_fail_by_name_returns_role_if_exists()
    {
        $role = Role::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect([$role->name => $role]));

        $result = $this->repository->findOrFailByName($role->name);

        $this->assertTrue($role->is($result));
    }

    public function test_find_or_fail_by_name_throws_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect());

        $this->expectException(RoleNotFoundException::class);

        $this->repository->findOrFailByName(fake()->unique()->word());
    }

    public function test_create_stores_role_and_forgets_cache()
    {
        $this->cacheService->expects($this->once())->method('invalidateCacheForAllLinks');

        $name = fake()->unique()->word();
        $role = $this->repository->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertTrue($this->repository->exists($name));
    }

    public function test_update_role_name_updates_name_and_clears_cache()
    {
        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedRole = $this->repository->updateName($role, $newName);

        $this->assertEquals($newName, $updatedRole->name);
    }

    public function test_deactivate_role_sets_active_to_false_and_clears_cache()
    {
        $role = Role::factory()->create(['is_active' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $deactivatedRole = $this->repository->deactivate($role);

        $this->assertFalse($deactivatedRole->is_active);
    }

    public function test_reactivate_role_sets_active_to_true_and_clears_cache()
    {
        $role = Role::factory()->inactive()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $activatedRole = $this->repository->reactivate($role);

        $this->assertTrue($activatedRole->is_active);
    }

    public function test_delete_role_soft_deletes_and_clears_cache()
    {
        $role = Role::factory()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $this->repository->delete($role);

        $this->assertSoftDeleted($role->fresh());
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Role::factory()->count(2)->make();

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
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
        $roles = Role::factory()->count(3)->create();

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putAllRoles')
            ->with(Role::all()->mapWithKeys(fn (Role $r) => [$r->name => $r]));

        $this->assertEqualsCanonicalizing(
            $roles->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_get_assigned_roles_for_model_caches_result()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->roles()->attach($role);

        $this->cacheService->expects($this->once())
            ->method('getModelRoleLinks')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelRoleLinks')
            ->with($user, collect([[
                'name' => $role->name,
                'denied' => 0,
            ]]));

        $roles = $this->repository->assignedToModel($user);

        $this->assertEquals($role->name, $roles->first()->name);
    }
}
