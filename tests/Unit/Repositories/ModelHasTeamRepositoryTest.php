<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Repositories\ModelHasTeamRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ModelHasTeamRepositoryTest extends TestCase
{
    protected ModelHasTeamRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ModelHasTeamRepository;
    }

    public function test_create_team_assignment()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $assignment = $this->repository->create($user, $team);

        $this->assertInstanceOf(ModelHasTeam::class, $assignment);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_teams'), [
            'team_id' => $team->id,
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
        ]);
    }

    public function test_get_for_model_and_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->repository->create($user, $team);

        $assignments = $this->repository->getForModelAndTeam($user, $team);

        $this->assertCount(1, $assignments);
        $this->assertInstanceOf(ModelHasTeam::class, $assignments->first());
    }

    public function test_get_recent_for_model_and_team_including_trashed()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $assignment = $this->repository->create($user, $team);
        $assignment->delete();

        $recent = $this->repository->getRecentForModelAndTeamIncludingTrashed($user, $team);

        $this->assertNotNull($recent);
        $this->assertTrue($recent->trashed());
    }

    public function test_delete_for_model_and_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->repository->create($user, $team);
        $result = $this->repository->deleteForModelAndTeam($user, $team);

        $this->assertTrue($result);

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams'), [
            'team_id' => $team->id,
            'model_id' => $user->id,
        ]);
    }
}
