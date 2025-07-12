<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Role\RoleNotFoundException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class RoleRepositoryTest extends TestCase
{
    protected RoleRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit.enabled', false);

        $cacheMock = $this->createMock(CacheService::class);
        $this->cacheService = $cacheMock;

        $this->repository = new RoleRepository($cacheMock);
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
            ->willReturn(collect([$role]));

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
            ->willReturn(collect([$role]));

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
        $this->cacheService->expects($this->once())->method('invalidateCacheForAllRoles');

        $name = fake()->unique()->word();
        $role = $this->repository->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertTrue($this->repository->exists($name));
    }

    public function test_update_role_updates_name_and_clears_cache()
    {
        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedRole = $this->repository->update($role, $newName);

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
            ->with(Role::all()->values());

        $this->assertEqualsCanonicalizing(
            $roles->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_active_returns_only_active_roles()
    {
        $inactive = Role::factory()->count(2)->inactive()->create();
        $active = Role::factory()->count(2)->create();

        $all = $inactive->concat($active);

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn($all);

        $result = $this->repository->active();

        $this->assertEqualsCanonicalizing(
            $active->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_roles()
    {
        $roles = Role::factory()->count(3)->create();
        $names = $roles->pluck('name');

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn($roles);

        $result = $this->repository->whereNameIn($names);

        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $roles->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_empty_collection_when_no_matches()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect());

        $result = $this->repository->whereNameIn(['nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_get_all_role_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->roles()->attach($role);

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelRoleNames')
            ->with($user, collect([$role->name]));

        $names = $this->repository->namesForModel($user);

        $this->assertContains($role->name, $names->toArray());
    }

    public function test_get_all_roles_for_model_returns_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->roles()->attach($role);

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(collect([$role->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect([$role]));

        $roles = $this->repository->forModel($user);

        $this->assertCount(1, $roles);
        $this->assertTrue($roles->first()->is($role));
    }

    public function test_get_all_roles_for_model_returns_empty_when_no_roles()
    {
        $user = User::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect());

        $roles = $this->repository->forModel($user);

        $this->assertCount(0, $roles);
    }

    public function test_active_for_model_returns_active_roles()
    {
        $user = User::factory()->create();
        $activeRole = Role::factory()->create();
        $inactiveRole = Role::factory()->inactive()->create();

        $user->roles()->attach([$activeRole->id, $inactiveRole->id]);

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(collect([$activeRole->name, $inactiveRole->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect([$activeRole, $inactiveRole]));

        $roles = $this->repository->activeForModel($user);

        $this->assertCount(1, $roles);
        $this->assertTrue($roles->first()->is($activeRole));
    }

    public function test_find_by_name_for_model_returns_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->roles()->attach($role);

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(collect([$role->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect([$role]));

        $result = $this->repository->findByNameForModel($user, $role->name);
        $this->assertTrue($result->is($role));
    }

    public function test_find_by_name_for_model_returns_null_if_not_found()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();

        $this->cacheService->expects($this->once())
            ->method('getModelRoleNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllRoles')
            ->willReturn(collect());

        $result = $this->repository->findByNameForModel($user, $roleName);
        $this->assertNull($result);
    }
}
