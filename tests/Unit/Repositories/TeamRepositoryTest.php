<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Exceptions\TeamNotFoundException;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\TeamRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class TeamRepositoryTest extends TestCase
{
    protected TeamRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TeamRepository::class);
        Cache::flush();
    }

    public function test_create_team_and_cache_is_invalidated()
    {
        $team = $this->repository->create('engineering');

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('engineering', $team->name);
        $this->assertDatabaseHas('teams', ['name' => 'engineering']);
    }

    public function test_all_returns_cached_teams()
    {
        Team::factory()->withName('alpha')->create();
        $this->repository->all();

        Team::factory()->withName('beta')->create();

        $teams = $this->repository->all();
        $this->assertCount(1, $teams);
    }

    public function test_find_by_name_returns_team()
    {
        $team = Team::factory()->withName('product')->create();

        $found = $this->repository->findByName('product');

        $this->assertTrue($found->is($team));
    }

    public function test_find_by_name_throws_if_not_found()
    {
        $this->expectException(TeamNotFoundException::class);

        $this->repository->findByName('missing');
    }

    public function test_get_active_filters_correctly()
    {
        $active = Team::factory()->create();
        Team::factory()->inactive()->create();

        $results = $this->repository->getActive();

        $this->assertTrue($results->contains($active));
        $this->assertCount(1, $results);
    }

    public function test_get_active_where_name_in_filters()
    {
        $names = ['dev', 'sales', 'marketing'];
        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $results = $this->repository->getActiveWhereNameIn(['sales', 'marketing']);

        $this->assertCount(2, $results);
        $this->assertEqualsCanonicalizing(['sales', 'marketing'], $results->pluck('name')->toArray());
    }

    public function test_get_active_for_model_returns_only_direct_active_teams()
    {
        $user = User::factory()->create();
        $activeTeam = Team::factory()->create();
        $inactiveTeam = Team::factory()->inactive()->create();

        $user->teams()->attach($activeTeam);
        $user->teams()->attach($inactiveTeam);

        $results = $this->repository->getActiveForModel($user);

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($activeTeam));
    }

    public function test_invalidate_cache_for_model()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $user->teams()->attach($team);
        $this->repository->getActiveNamesForModel($user);

        $this->repository->invalidateCacheForModel($user);

        $key = 'gatekeeper.teams.'.$user->getMorphClass().'.'.$user->getKey();
        $this->assertNull(Cache::get($key));
    }
}
