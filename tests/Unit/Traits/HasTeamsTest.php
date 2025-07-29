<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class HasTeamsTest extends TestCase
{
    private GatekeeperForModelService $gatekeeperForModelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatekeeperForModelService = app(GatekeeperForModelService::class);
    }

    public function test_assign_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('addModelToTeam')->with($user, $team)->once();

        $user->addToTeam($team);
    }

    public function test_assign_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('addModelToAllTeams')->with($user, $teams)->once();

        $user->addToAllTeams($teams);
    }

    public function test_unassign_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('removeModelFromTeam')->with($user, $team)->once();

        $user->removeFromTeam($team);
    }

    public function test_unassign_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('removeModelFromAllTeams')->with($user, $teams)->once();

        $user->removeFromAllTeams($teams);
    }

    public function test_deny_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyTeamFromModel')->with($user, $team)->once();

        $user->denyTeam($team);
    }

    public function test_deny_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyAllTeamsFromModel')->with($user, $teams)->once();

        $user->denyAllTeams($teams);
    }

    public function test_has_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelOnTeam')->with($user, $team)->once();

        $user->onTeam($team);
    }

    public function test_has_any_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelOnAnyTeam')->with($user, $teams)->once();

        $user->onAnyTeam($teams);
    }

    public function test_has_all_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelOnAllTeams')->with($user, $teams)->once();

        $user->onAllTeams($teams);
    }
}
