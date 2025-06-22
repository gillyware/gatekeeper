<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Facade;

class HasTeamsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
        Gatekeeper::spy();
    }

    public function test_add_to_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'engineering';

        $user->addToTeam($team);

        Gatekeeper::shouldHaveReceived('addModelToTeam')->with($user, $team)->once();
    }

    public function test_add_to_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['engineering', 'marketing'];

        $user->addToTeams($teams);

        Gatekeeper::shouldHaveReceived('addModelToTeams')->with($user, $teams)->once();
    }

    public function test_add_to_teams_delegates_with_arrayable()
    {
        $user = User::factory()->create();
        $teams = collect(['engineering', 'marketing']);

        $user->addToTeams($teams);

        Gatekeeper::shouldHaveReceived('addModelToTeams')->with($user, $teams)->once();
    }

    public function test_remove_from_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'engineering';

        $user->removeFromTeam($team);

        Gatekeeper::shouldHaveReceived('removeModelFromTeam')->with($user, $team)->once();
    }

    public function test_remove_from_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['engineering', 'marketing'];

        $user->removeFromTeams($teams);

        Gatekeeper::shouldHaveReceived('removeModelFromTeams')->with($user, $teams)->once();
    }

    public function test_on_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $team = 'engineering';

        $user->onTeam($team);

        Gatekeeper::shouldHaveReceived('modelOnTeam')->with($user, $team)->once();
    }

    public function test_on_any_team_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['engineering', 'marketing'];

        $user->onAnyTeam($teams);

        Gatekeeper::shouldHaveReceived('modelOnAnyTeam')->with($user, $teams)->once();
    }

    public function test_on_all_teams_delegates_to_facade()
    {
        $user = User::factory()->create();
        $teams = ['engineering', 'marketing'];

        $user->onAllTeams($teams);

        Gatekeeper::shouldHaveReceived('modelOnAllTeams')->with($user, $teams)->once();
    }
}
