<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class HasTeamsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.teams', true);
    }

    public function test_we_can_assign_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $result = $user->assignTeam($teamName);

        $this->assertTrue($result);
        $this->assertDatabaseHas('model_has_teams', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_assigning_duplicate_team_does_not_duplicate()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $user->assignTeam($teamName);
        $result = $user->assignTeam($teamName);

        $this->assertTrue($result);

        $this->assertCount(1, ModelHasTeam::forModel($user)
            ->where('team_id', $team->id)
            ->withTrashed()
            ->get());
    }

    public function test_we_can_revoke_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        $team = Team::factory()->withName($teamName)->create();

        $user->assignTeam($teamName);
        $user->revokeTeam($teamName);

        $this->assertSoftDeleted('model_has_teams', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_revoke_team_does_nothing_if_not_assigned()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $result = $user->revokeTeam($teamName);

        $this->assertSame(0, $result);
    }

    public function test_we_can_check_if_user_has_a_team()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->assignTeam($teamName);

        $this->assertTrue($user->hasTeam($teamName));
    }

    public function test_has_team_returns_false_if_team_inactive()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->inactive()->create();

        $user->assignTeam($teamName);

        $this->assertFalse($user->hasTeam($teamName));
    }

    public function test_has_team_returns_false_if_team_missing()
    {
        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $this->assertFalse($user->hasTeam($teamName));
    }

    public function test_has_team_returns_false_when_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $this->assertFalse($user->hasTeam($teamName));
    }

    public function test_assign_team_throws_if_teams_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->assignTeam($teamName);
    }

    public function test_revoke_team_throws_if_teams_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $teamName = fake()->unique()->word();
        Team::factory()->withName($teamName)->create();

        $user->revokeTeam($teamName);
    }

    public function test_it_throws_if_team_does_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);

        $user = User::factory()->create();
        $user->assignTeam('nonexistent_team');
    }
}
