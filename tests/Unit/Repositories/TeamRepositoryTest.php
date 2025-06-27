<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\TeamNotFoundException;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\CacheRepository;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

class TeamRepositoryTest extends TestCase
{
    protected TeamRepository $repository;

    protected MockInterface $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheRepository = Mockery::mock(CacheRepository::class);
        $this->app->instance(CacheRepository::class, $this->cacheRepository);

        $this->repository = $this->app->make(TeamRepository::class);
    }

    public function test_create_team_and_cache_is_invalidated()
    {
        $this->cacheRepository->shouldReceive('forget')
            ->once()
            ->with('teams');

        $team = $this->repository->create('engineering');

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('engineering', $team->name);
        $this->assertDatabaseHas('teams', ['name' => 'engineering']);
    }

    public function test_all_returns_cached_teams()
    {
        $cached = Team::factory()->count(2)->make();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn($cached);

        $teams = $this->repository->all();

        $this->assertCount(2, $teams);
        $this->assertEquals($cached->pluck('id')->toArray(), $teams->pluck('id')->toArray());
    }

    public function test_all_fetches_teams_and_caches_if_not_cached()
    {
        $teams = Team::factory()->count(3)->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn(null);

        $this->cacheRepository->shouldReceive('put')
            ->once()
            ->with('teams', \Mockery::on(fn ($arg) => $arg instanceof Collection && $arg->count() === 3));

        $result = $this->repository->all();

        $this->assertEqualsCanonicalizing($teams->pluck('id')->toArray(), $result->pluck('id')->toArray());
    }

    public function test_find_by_name_returns_team()
    {
        $team = Team::factory()->create();

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn(collect([$team]));

        $found = $this->repository->findByName($team->name);

        $this->assertTrue($found->is($team));
    }

    public function test_find_by_name_throws_if_not_found()
    {
        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn(collect());

        $this->expectException(TeamNotFoundException::class);

        $this->repository->findByName('nonexistent');
    }

    public function test_find_by_name_bubbles_unexpected_exception()
    {
        $mock = Mockery::mock(TeamRepository::class.'[all]', [$this->cacheRepository]);
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('all')->andThrow(new \RuntimeException('whoops'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('whoops');

        $mock->findByName('whatever');
    }

    public function test_get_active_filters_correctly()
    {
        $active = Team::factory()->create();
        $inactive = Team::factory()->inactive()->create();
        $all = collect([$active, $inactive]);

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn($all);

        $results = $this->repository->getActive();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($active));
    }

    public function test_get_active_where_name_in_filters()
    {
        $t1 = Team::factory()->create(['name' => 'dev']);
        $t2 = Team::factory()->create(['name' => 'sales']);
        $t3 = Team::factory()->create(['name' => 'marketing']);

        $this->cacheRepository->shouldReceive('get')
            ->once()
            ->with('teams')
            ->andReturn(collect([$t1, $t2, $t3]));

        $results = $this->repository->getActiveWhereNameIn(['sales', 'marketing']);

        $this->assertCount(2, $results);
        $this->assertEqualsCanonicalizing(['sales', 'marketing'], $results->pluck('name')->toArray());
    }

    public function test_get_active_for_model_returns_only_direct_active_teams()
    {
        $user = User::factory()->create();
        $active = Team::factory()->create();
        $inactive = Team::factory()->inactive()->create();

        $user->teams()->attach([$active->id, $inactive->id]);

        $cacheKey = "teams.{$user->getMorphClass()}.{$user->getKey()}";
        $this->cacheRepository->shouldReceive('get')->with($cacheKey)->once()->andReturn(collect([$active->name]));

        $this->cacheRepository->shouldReceive('get')->with('teams')->once()->andReturn(collect([$active, $inactive]));

        $results = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($active));
    }

    public function test_get_active_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $user->teams()->attach($team);

        $cacheKey = "teams.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('get')->with($cacheKey)->once()->andReturn(null);
        $this->cacheRepository->shouldReceive('put')->once();

        $result = $this->repository->getActiveNamesForModel($user);

        $this->assertTrue($result->contains($team->name));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();

        $cacheKey = "teams.{$user->getMorphClass()}.{$user->getKey()}";

        $this->cacheRepository->shouldReceive('forget')
            ->once()
            ->with($cacheKey);

        $this->repository->invalidateCacheForModel($user);
    }
}
