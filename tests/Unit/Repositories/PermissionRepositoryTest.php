<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class PermissionRepositoryTest extends TestCase
{
    protected PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository;
        Cache::flush();
    }

    public function test_create_stores_permission_and_forgets_cache()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('gatekeeper.permissions');

        $name = fake()->unique()->word();
        $permission = $this->repository->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Permission::factory()->count(2)->create();
        Cache::put('gatekeeper.permissions', $cached);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertEquals($cached->pluck('id')->toArray(), $result->pluck('id')->toArray());
    }

    public function test_all_caches_result_if_not_cached()
    {
        Cache::forget('gatekeeper.permissions');
        $permissions = Permission::factory()->count(3)->create();

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_find_by_name_throws_when_not_found()
    {
        $this->expectException(\Braxey\Gatekeeper\Exceptions\PermissionNotFoundException::class);
        $this->repository->findByName('nonexistent');
    }

    public function test_find_by_name_returns_permission()
    {
        $permission = Permission::factory()->create();
        $result = $this->repository->findByName($permission->name);

        $this->assertTrue($permission->is($result));
    }

    public function test_get_active_returns_only_active_permissions()
    {
        Permission::factory()->count(2)->inactive()->create();
        $active = Permission::factory()->count(2)->create();

        $result = $this->repository->getActive();

        $this->assertEqualsCanonicalizing(
            $active->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_get_active_where_name_in_filters_names()
    {
        $p1 = Permission::factory()->create();
        $p2 = Permission::factory()->inactive()->create();

        $result = $this->repository->getActiveWhereNameIn([$p1->name, $p2->name]);

        $this->assertCount(1, $result);
        $this->assertEquals($p1->name, $result->first()->name);
    }

    public function test_get_active_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $key = "gatekeeper.permissions.{$user->getMorphClass()}.{$user->getKey()}";
        Cache::forget($key);

        $names = $this->repository->getActiveNamesForModel($user);
        $this->assertContains($permission->name, $names->toArray());
        $this->assertTrue(Cache::has($key));
    }

    public function test_get_active_for_model_returns_active_permissions()
    {
        $user = User::factory()->create();
        $active = Permission::factory()->create();
        $inactive = Permission::factory()->inactive()->create();

        $user->permissions()->attach([$active->id, $inactive->id]);

        $permissions = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->is($active));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();
        $key = "gatekeeper.permissions.{$user->getMorphClass()}.{$user->getKey()}";

        Cache::put($key, ['test']);
        $this->assertTrue(Cache::has($key));

        $this->repository->invalidateCacheForModel($user);
        $this->assertFalse(Cache::has($key));
    }
}
