<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\RoleNotFoundException;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Repositories\CacheRepository;
use Braxey\Gatekeeper\Repositories\RoleRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

class RoleRepositoryTest extends TestCase
{
    protected RoleRepository $repository;

    protected MockInterface $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheRepository = Mockery::mock(CacheRepository::class);
        $this->app->instance(CacheRepository::class, $this->cacheRepository);

        $this->repository = $this->app->make(RoleRepository::class);
    }

    public function test_create_role()
    {
        $this->cacheRepository->shouldReceive('forget')
            ->once()
            ->with('roles');

        $name = fake()->unique()->word();

        $role = $this->repository->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($name, $role->name);
        $this->assertDatabaseHas('roles', ['name' => $name]);
    }

    public function test_all_returns_cached_roles_if_available()
    {
        $cached = Role::factory()->count(2)->make();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn($cached);

        $result = $this->repository->all();

        $this->assertEquals($cached->pluck('name'), $result->pluck('name'));
    }

    public function test_all_fetches_roles_and_caches_if_not_cached()
    {
        $roles = Role::factory()->count(2)->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn(null);

        $this->cacheRepository->shouldReceive('put')
            ->once()
            ->with('roles', \Mockery::on(fn ($arg) => $arg instanceof Collection && $arg->count() === 2));

        $result = $this->repository->all();

        $this->assertCount(2, $result);
    }

    public function test_find_by_name_returns_role_if_exists()
    {
        $role = Role::factory()->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn(collect([$role]));

        $result = $this->repository->findByName($role->name);

        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals($role->name, $result->name);
    }

    public function test_find_by_name_throws_if_not_found()
    {
        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn(collect());

        $this->expectException(RoleNotFoundException::class);

        $this->repository->findByName('nonexistent');
    }

    public function test_get_active_roles_filters_active()
    {
        $active = Role::factory()->count(2)->create(['is_active' => true]);
        $inactive = Role::factory()->count(1)->create(['is_active' => false]);
        $all = $active->concat($inactive);

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn($all);

        $result = $this->repository->getActive();

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing($active->pluck('id')->toArray(), $result->pluck('id')->toArray());
    }

    public function test_get_active_where_name_in()
    {
        $roles = Role::factory()->count(3)->create(['is_active' => true]);

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn($roles);

        $names = $roles->take(2)->pluck('name');

        $result = $this->repository->getActiveWhereNameIn($names);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing($names->toArray(), $result->pluck('name')->toArray());
    }

    public function test_get_active_for_model()
    {
        $user = User::factory()->create();
        $active = Role::factory()->create(['is_active' => true]);
        $inactive = Role::factory()->inactive()->create();

        $user->roles()->attach([$active->id, $inactive->id]);

        $modelKey = "roles.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('get')->with($modelKey)->once()->andReturn(collect([$active->name]));
        $this->cacheRepository->shouldReceive('get')->with('roles')->once()->andReturn(collect([$active, $inactive]));

        $result = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($active));
    }

    public function test_get_active_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        $modelKey = "roles.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('get')->with($modelKey)->once()->andReturn(null);
        $this->cacheRepository->shouldReceive('put')->once();

        $result = $this->repository->getActiveNamesForModel($user);

        $this->assertTrue($result->contains($role->name));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();

        $modelKey = "roles.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('forget')->once()->with($modelKey);

        $this->repository->invalidateCacheForModel($user);
    }
}
