<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Team\TeamNotFoundException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class TeamRepositoryTest extends TestCase
{
    protected TeamRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit.enabled', false);

        $cacheMock = $this->createMock(CacheService::class);
        $this->cacheService = $cacheMock;

        $this->repository = new TeamRepository($cacheMock);
    }

    public function test_team_exists_returns_true_if_exists()
    {
        $team = Team::factory()->create();

        $this->assertTrue($this->repository->exists($team->name));
    }

    public function test_team_exists_returns_false_if_not_exists()
    {
        $this->assertFalse($this->repository->exists(fake()->unique()->word()));
    }

    public function test_find_by_name_returns_team_if_exists()
    {
        $team = Team::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect([$team]));

        $result = $this->repository->findByName($team->name);

        $this->assertTrue($team->is($result));
    }

    public function test_find_by_name_returns_null_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect());

        $result = $this->repository->findByName(fake()->unique()->word());

        $this->assertNull($result);
    }

    public function test_find_or_fail_by_name_returns_team_if_exists()
    {
        $team = Team::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect([$team]));

        $result = $this->repository->findOrFailByName($team->name);

        $this->assertTrue($team->is($result));
    }

    public function test_find_or_fail_by_name_throws_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect());

        $this->expectException(TeamNotFoundException::class);

        $this->repository->findOrFailByName(fake()->unique()->word());
    }

    public function test_create_stores_team_and_forgets_cache()
    {
        $this->cacheService->expects($this->once())->method('invalidateCacheForAllTeams');

        $name = fake()->unique()->word();
        $team = $this->repository->create($name);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertTrue($this->repository->exists($name));
    }

    public function test_update_team_updates_name_and_clears_cache()
    {
        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedTeam = $this->repository->update($team, $newName);

        $this->assertEquals($newName, $updatedTeam->name);
    }

    public function test_deactivate_team_sets_active_to_false_and_clears_cache()
    {
        $team = Team::factory()->create(['is_active' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $deactivatedTeam = $this->repository->deactivate($team);

        $this->assertFalse($deactivatedTeam->is_active);
    }

    public function test_reactivate_team_sets_active_to_true_and_clears_cache()
    {
        $team = Team::factory()->inactive()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $activatedTeam = $this->repository->reactivate($team);

        $this->assertTrue($activatedTeam->is_active);
    }

    public function test_delete_team_soft_deletes_and_clears_cache()
    {
        $team = Team::factory()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $this->repository->delete($team);

        $this->assertSoftDeleted($team->fresh());
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Team::factory()->count(2)->make();

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
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
        $teams = Team::factory()->count(3)->create();

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putAllTeams')
            ->with(Team::all()->values());

        $this->assertEqualsCanonicalizing(
            $teams->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_active_returns_only_active_teams()
    {
        $inactive = Team::factory()->count(2)->inactive()->create();
        $active = Team::factory()->count(2)->create();

        $all = $inactive->concat($active);

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn($all);

        $result = $this->repository->active();

        $this->assertEqualsCanonicalizing(
            $active->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_teams()
    {
        $teams = Team::factory()->count(3)->create();
        $names = $teams->pluck('name');

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn($teams);

        $result = $this->repository->whereNameIn($names);

        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $teams->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_empty_collection_when_no_matches()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect());

        $result = $this->repository->whereNameIn(['nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_get_all_team_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $user->teams()->attach($team);

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelTeamNames')
            ->with($user, collect([$team->name]));

        $names = $this->repository->namesForModel($user);

        $this->assertContains($team->name, $names->toArray());
    }

    public function test_get_all_teams_for_model_returns_teams()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $user->teams()->attach($team);

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(collect([$team->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect([$team]));

        $teams = $this->repository->forModel($user);

        $this->assertCount(1, $teams);
        $this->assertTrue($teams->first()->is($team));
    }

    public function test_get_all_teams_for_model_returns_empty_when_no_teams()
    {
        $user = User::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect());

        $teams = $this->repository->forModel($user);

        $this->assertCount(0, $teams);
    }

    public function test_active_for_model_returns_active_teams()
    {
        $user = User::factory()->create();
        $activeTeam = Team::factory()->create();
        $inactiveTeam = Team::factory()->inactive()->create();

        $user->teams()->attach([$activeTeam->id, $inactiveTeam->id]);

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(collect([$activeTeam->name, $inactiveTeam->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect([$activeTeam, $inactiveTeam]));

        $teams = $this->repository->activeForModel($user);

        $this->assertCount(1, $teams);
        $this->assertTrue($teams->first()->is($activeTeam));
    }

    public function test_find_by_name_for_model_returns_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $user->teams()->attach($team);

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(collect([$team->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect([$team]));

        $result = $this->repository->findByNameForModel($user, $team->name);
        $this->assertTrue($result->is($team));
    }

    public function test_find_by_name_for_model_returns_null_if_not_found()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();

        $this->cacheService->expects($this->once())
            ->method('getModelTeamNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllTeams')
            ->willReturn(collect());

        $result = $this->repository->findByNameForModel($user, $teamName);
        $this->assertNull($result);
    }
}
