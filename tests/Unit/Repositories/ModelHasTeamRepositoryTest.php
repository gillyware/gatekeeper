<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class ModelHasTeamRepositoryTest extends TestCase
{
    protected ModelHasTeamRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(ModelHasTeamRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(ModelHasTeamRepository::class);
    }

    public function test_it_can_check_if_a_team_is_assigned_to_any_model()
    {
        $team = Team::factory()->create();

        $this->assertFalse($this->repository->existsForEntity($team));

        $user = User::factory()->create();
        $this->repository->assignToModel($user, $team);

        $this->assertTrue($this->repository->existsForEntity($team));
    }

    public function test_it_can_create_model_team_record()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelTeamLinks')
            ->with($user);

        $record = $this->repository->assignToModel($user, $team);

        $this->assertInstanceOf(ModelHasTeam::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_it_can_soft_delete_model_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelTeamLinks')
            ->with($user);

        $this->repository->assignToModel($user, $team);

        $this->assertTrue($this->repository->unassignFromModel($user, $team));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }
}
