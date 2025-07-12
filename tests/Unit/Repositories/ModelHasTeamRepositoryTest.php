<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
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

        $cacheMock = $this->createMock(CacheService::class);
        $this->cacheService = $cacheMock;

        $this->repository = new ModelHasTeamRepository($cacheMock, app()->make(ModelMetadataService::class));
    }

    public function test_it_can_check_if_a_team_is_assigned_to_any_model()
    {
        $team = Team::factory()->create();

        $this->assertFalse($this->repository->existsForTeam($team));

        $user = User::factory()->create();
        $this->repository->create($user, $team);

        $this->assertTrue($this->repository->existsForTeam($team));
    }

    public function test_it_can_create_model_team_record()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelTeamNames')
            ->with($user);

        $record = $this->repository->create($user, $team);

        $this->assertInstanceOf(ModelHasTeam::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_it_can_get_model_team_records()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->repository->create($user, $team);

        $records = $this->repository->getForModelAndTeam($user, $team);

        $this->assertCount(1, $records);
        $this->assertInstanceOf(ModelHasTeam::class, $records->first());
    }

    public function test_it_can_get_most_recent_model_team_including_trashed()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->repository->create($user, $team);
        $record = $this->repository->getRecentForModelAndTeamIncludingTrashed($user, $team);

        $this->assertInstanceOf(ModelHasTeam::class, $record);
    }

    public function test_it_can_soft_delete_model_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelTeamNames')
            ->with($user);

        $this->repository->create($user, $team);

        $this->assertTrue($this->repository->deleteForModelAndTeam($user, $team));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }
}
