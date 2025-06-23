<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Exceptions\TeamNotFoundException;
use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class HasTeamsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.teams', true);
    }

    public function test_we_can_add_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $result = $user->addToTeam($teamName);

        $this->assertTrue($result);
        $this->assertDatabaseHas('model_has_teams', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_adding_duplicate_team_does_not_duplicate()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $user->addToTeam($teamName);
        $result = $user->addToTeam($teamName);

        $this->assertTrue($result);

        $this->assertCount(1, ModelHasTeam::forModel($user)
            ->where('team_id', $team->id)
            ->withTrashed()
            ->get());
    }

    public function test_add_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = collect([
            Team::factory()->withName($name1 = fake()->unique()->word())->create(),
            Team::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->addToTeams([$name1, $name2]);

        foreach ($teams as $team) {
            $this->assertDatabaseHas('model_has_teams', [
                'team_id' => $team->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_add_multiple_teams_with_arrayable()
    {
        $user = User::factory()->create();
        $teams = collect([
            Team::factory()->withName($name1 = fake()->unique()->word())->create(),
            Team::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->addToTeams(collect([$name1, $name2]));

        foreach ($teams as $team) {
            $this->assertDatabaseHas('model_has_teams', [
                'team_id' => $team->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_we_can_remove_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $user->addToTeam($teamName);
        $user->removeFromTeam($teamName);

        $this->assertSoftDeleted('model_has_teams', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_remove_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = collect([
            Team::factory()->withName($name1 = fake()->unique()->word())->create(),
            Team::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->addToTeams([$name1, $name2]);
        $user->removeFromTeams([$name1, $name2]);

        foreach ($teams as $team) {
            $this->assertSoftDeleted('model_has_teams', [
                'team_id' => $team->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_remove_multiple_teams_with_arrayable()
    {
        $user = User::factory()->create();
        $teams = collect([
            Team::factory()->withName($name1 = fake()->unique()->word())->create(),
            Team::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->addToTeams(collect([$name1, $name2]));
        $user->removeFromTeams(collect([$name1, $name2]));

        foreach ($teams as $team) {
            $this->assertSoftDeleted('model_has_teams', [
                'team_id' => $team->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_we_can_check_if_user_has_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->addToTeam($teamName);

        $this->assertTrue($user->onTeam($teamName));
    }

    public function test_has_team_returns_false_if_team_inactive()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->inactive()->create();

        $user->addToTeam($teamName);

        $this->assertFalse($user->onTeam($teamName));
    }

    public function test_has_team_returns_false_if_team_missing()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $this->assertFalse($user->onTeam($teamName));
    }

    public function test_has_team_returns_false_when_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $this->assertFalse($user->onTeam($teamName));
    }

    public function test_add_team_throws_if_teams_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->addToTeam($teamName);
    }

    public function test_remove_team_throws_if_teams_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->removeFromTeam($teamName);
    }

    public function test_on_any_team_returns_true_if_one_matches()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $user->addToTeam($names[1]);

        $this->assertTrue($user->onAnyTeam($names));
    }

    public function test_on_any_team_returns_true_if_one_matches_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $user->addToTeam($names[1]);

        $this->assertTrue($user->onAnyTeam(collect($names)));
    }

    public function test_on_any_team_returns_false_if_none_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $this->assertFalse($user->onAnyTeam($names));
    }

    public function test_on_all_teams_returns_false_if_any_missing()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $user->addToTeam($names[0]);

        $this->assertFalse($user->onAllTeams($names));
    }

    public function test_on_all_teams_returns_true_if_all_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $user->addToTeams($names);

        $this->assertTrue($user->onAllTeams($names));
    }

    public function test_on_all_teams_returns_true_if_all_match_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Team::factory()->withName($name)->create();
        }

        $user->addToTeams(collect($names));

        $this->assertTrue($user->onAllTeams(collect($names)));
    }

    public function test_it_throws_if_team_does_not_exist()
    {
        $this->expectException(TeamNotFoundException::class);

        $user = User::factory()->create();
        $user->addToTeam('nonexistent_team');
    }
}
