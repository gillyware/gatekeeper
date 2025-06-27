<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\PermissionNotFoundException;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Repositories\CacheRepository;
use Braxey\Gatekeeper\Repositories\PermissionRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class PermissionRepositoryTest extends TestCase
{
    protected PermissionRepository $repository;

    protected MockInterface $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheRepository = Mockery::mock(CacheRepository::class);
        $this->app->instance(CacheRepository::class, $this->cacheRepository);

        $this->repository = $this->app->make(PermissionRepository::class);
    }

    public function test_create_stores_permission_and_forgets_cache()
    {
        $this->cacheRepository
            ->shouldReceive('forget')
            ->once()
            ->with('permissions');

        $name = fake()->unique()->word();
        $permission = $this->repository->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Permission::factory()->count(2)->make();

        $this->cacheRepository
            ->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn($cached);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertEquals($cached->pluck('id')->toArray(), $result->pluck('id')->toArray());
    }

    public function test_all_caches_result_if_not_cached()
    {
        $permissions = Permission::factory()->count(3)->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn(null);

        $this->cacheRepository->shouldReceive('put')
            ->once()
            ->with('permissions', Mockery::on(fn ($arg) => $arg->count() === 3));

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_find_by_name_throws_when_not_found()
    {
        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn(collect());

        $this->expectException(PermissionNotFoundException::class);

        $this->repository->findByName('nonexistent');
    }

    public function test_find_by_name_bubbles_unexpected_exceptions()
    {
        $this->partialMock(PermissionRepository::class, function ($mock) {
            $mock->shouldReceive('all')->andThrow(new \RuntimeException('unexpected'));
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unexpected');

        app(PermissionRepository::class)->findByName('whatever');
    }

    public function test_find_by_name_returns_permission()
    {
        $permission = Permission::factory()->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn(collect([$permission]));

        $result = $this->repository->findByName($permission->name);

        $this->assertTrue($permission->is($result));
    }

    public function test_get_active_returns_only_active_permissions()
    {
        $inactive = Permission::factory()->count(2)->inactive()->create();
        $active = Permission::factory()->count(2)->create();

        $all = $inactive->concat($active);

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn($all);

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

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn(collect([$p1, $p2]));

        $result = $this->repository->getActiveWhereNameIn([$p1->name, $p2->name]);

        $this->assertCount(1, $result);
        $this->assertEquals($p1->name, $result->first()->name);
    }

    public function test_get_active_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $user->permissions()->attach($permission);

        $key = "permissions.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('get')->with($key)->once()->andReturn(null);
        $this->cacheRepository->shouldReceive('put')->once();

        $names = $this->repository->getActiveNamesForModel($user);

        $this->assertContains($permission->name, $names->toArray());
    }

    public function test_get_active_for_model_returns_active_permissions()
    {
        $user = User::factory()->create();
        $active = Permission::factory()->create();
        $inactive = Permission::factory()->inactive()->create();

        $user->permissions()->attach([$active->id, $inactive->id]);

        $key = "permissions.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('get')->with($key)->once()->andReturn(collect([$active->name]));

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn(collect([$active, $inactive]));

        $permissions = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->is($active));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();
        $key = "permissions.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('forget')->once()->with($key);

        $this->repository->invalidateCacheForModel($user);
    }
}
