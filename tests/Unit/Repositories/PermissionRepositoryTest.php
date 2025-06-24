<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\PermissionNotFoundException;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use Mockery;

class PermissionRepositoryTest extends TestCase
{
    protected PermissionRepository $repository;

    protected TaggedCache $taggedCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taggedCache = Mockery::mock(TaggedCache::class);
        Cache::shouldReceive('tags')->with('gatekeeper')->andReturn($this->taggedCache)->byDefault();

        $this->taggedCache->shouldReceive('get')->andReturn(null)->byDefault();
        $this->taggedCache->shouldReceive('put')->andReturn(true)->byDefault();
        $this->taggedCache->shouldReceive('forget')->andReturn(true)->byDefault();
        $this->taggedCache->shouldReceive('has')->andReturn(false)->byDefault();

        $this->repository = new PermissionRepository;
    }

    public function test_create_stores_permission_and_forgets_cache()
    {
        $this->taggedCache->shouldReceive('forget')->once()->with('gatekeeper.permissions');

        $name = fake()->unique()->word();
        $permission = $this->repository->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Permission::factory()->count(2)->make();
        $this->taggedCache->shouldReceive('get')->with('gatekeeper.permissions')->once()->andReturn($cached);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertEquals($cached->pluck('id')->toArray(), $result->pluck('id')->toArray());
    }

    public function test_all_caches_result_if_not_cached()
    {
        $this->taggedCache->shouldReceive('get')->once()->andReturn(null);
        $this->taggedCache->shouldReceive('put')->once();

        $permissions = Permission::factory()->count(3)->create();

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_find_by_name_throws_when_not_found()
    {
        $this->expectException(PermissionNotFoundException::class);
        $this->repository->findByName('nonexistent');
    }

    public function test_find_by_name_returns_permission()
    {
        $permission = Permission::factory()->create();
        $this->repository->all();

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

        $this->taggedCache->shouldReceive('get')->with($key)->once()->andReturn(null);
        $this->taggedCache->shouldReceive('put')->once();
        $this->taggedCache->shouldReceive('has')->with($key)->andReturn(true);

        $names = $this->repository->getActiveNamesForModel($user);

        $this->assertContains($permission->name, $names->toArray());
        $this->assertTrue(Cache::tags('gatekeeper')->has($key));
    }

    public function test_get_active_for_model_returns_active_permissions()
    {
        $user = User::factory()->create();
        $active = Permission::factory()->create();
        $inactive = Permission::factory()->inactive()->create();

        $user->permissions()->attach([$active->id, $inactive->id]);

        $this->repository->getActiveNamesForModel($user);

        $permissions = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->is($active));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();
        $key = "gatekeeper.permissions.{$user->getMorphClass()}.{$user->getKey()}";

        $this->taggedCache->shouldReceive('forget')->once()->with($key);

        $this->repository->invalidateCacheForModel($user);
    }
}
