<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\RoleNotFoundException;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use Mockery;

class RoleRepositoryTest extends TestCase
{
    protected RoleRepository $repository;

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

        $this->repository = new RoleRepository;
    }

    public function test_create_role()
    {
        $this->taggedCache->shouldReceive('forget')->once();

        $name = fake()->unique()->word();

        $role = $this->repository->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($name, $role->name);
    }

    public function test_all_returns_cached_roles_if_available()
    {
        $cached = Role::factory()->count(2)->make();
        $this->taggedCache->shouldReceive('get')->once()->andReturn($cached);

        $result = $this->repository->all();

        $this->assertEquals($cached->pluck('name'), $result->pluck('name'));
    }

    public function test_all_fetches_roles_and_caches_if_not_cached()
    {
        $this->taggedCache->shouldReceive('get')->once()->andReturn(null);
        $this->taggedCache->shouldReceive('put')->once();

        Role::factory()->count(2)->create();

        $result = $this->repository->all();

        $this->assertCount(2, $result);
    }

    public function test_find_by_name_returns_role_if_exists()
    {
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $role = $this->repository->findByName($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($name, $role->name);
    }

    public function test_find_by_name_throws_if_not_found()
    {
        $this->expectException(RoleNotFoundException::class);

        $this->repository->findByName('nonexistent');
    }

    public function test_get_active_roles()
    {
        Role::factory()->count(2)->create(['is_active' => true]);
        Role::factory()->count(1)->create(['is_active' => false]);

        $active = $this->repository->getActive();

        $this->assertCount(2, $active);
    }

    public function test_get_active_where_name_in()
    {
        $roles = Role::factory()->count(3)->create(['is_active' => true]);
        $names = $roles->take(2)->pluck('name');

        $result = $this->repository->getActiveWhereNameIn($names);

        $this->assertCount(2, $result);
    }

    public function test_get_active_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['is_active' => true]);

        $user->roles()->attach($role);

        $this->taggedCache->shouldReceive('get')->once()->andReturn(null);
        $this->taggedCache->shouldReceive('put')->once();

        $result = $this->repository->getActiveNamesForModel($user);

        $this->assertTrue($result->contains($role->name));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();

        $this->taggedCache->shouldReceive('forget')->once();

        $this->repository->invalidateCacheForModel($user);
    }
}
