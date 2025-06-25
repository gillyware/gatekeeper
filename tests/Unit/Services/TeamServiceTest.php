<?php

namespace Braxey\Gatekeeper\Tests\Unit\Services;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithTeamsException;
use Braxey\Gatekeeper\Exceptions\TeamsFeatureDisabledException;
use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Models\AuditLog;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\TeamService;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class TeamServiceTest extends TestCase
{
    protected TeamService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.teams', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(TeamService::class);
        $this->service->actingAs($this->user);
    }

    public function test_create_team()
    {
        $name = fake()->unique()->word();

        $team = $this->service->create($name);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals($name, $team->name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $createTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_CREATE, $createTeamLog->action);
        $this->assertEquals($name, $createTeamLog->metadata['name']);
        $this->assertTrue($this->user->is($createTeamLog->actionBy));
        $this->assertNull($createTeamLog->actionTo);
    }

    public function test_create_team_throws_if_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $this->service->create(fake()->unique()->word());
    }

    public function test_add_model_to_team()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $result = $this->service->addModelTo($user, $name);

        $this->assertTrue($result);
        $this->assertDatabaseHas('model_has_teams', [
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
            'team_id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_add_model_to_team_is_idempotent()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $result = $this->service->addModelTo($user, $name);

        $this->assertTrue($result);
    }

    public function test_add_model_to_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->addModelToAll($user, $teams);

        $this->assertTrue($user->onAllTeams($teams));
    }

    public function test_remove_model_from_team()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $result = $this->service->removeModelFrom($user, $name);

        $this->assertTrue($result);
        $this->assertSoftDeleted('model_has_teams', [
            'team_id' => $team->id,
            'model_id' => $user->id,
        ]);
    }

    public function test_remove_model_from_multiple_teams()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        $teams = collect($names)->map(fn ($name) => Team::factory()->withName($name)->create());

        $this->service->addModelToAll($user, $names);
        $result = $this->service->removeModelFromAll($user, $names);

        $this->assertTrue($result);
        $teams->each(function ($team) use ($user) {
            $this->assertSoftDeleted('model_has_teams', [
                'team_id' => $team->id,
                'model_id' => $user->id,
            ]);
        });
    }

    public function test_model_on_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->addModelTo($user, $team);

        $this->assertTrue($this->service->modelOn($user, $team));
    }

    public function test_model_on_team_returns_false_if_team_inactive()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->inactive()->create();

        $this->service->addModelTo($user, $name);

        $this->assertFalse($this->service->modelOn($user, $name));
    }

    public function test_model_on_any_team()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelTo($user, $names[1]);

        $this->assertTrue($this->service->modelOnAny($user, $names));
    }

    public function test_model_on_all_teams()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelToAll($user, $names);

        $this->assertTrue($this->service->modelOnAll($user, $names));
    }

    public function test_model_on_all_teams_fails_if_one_missing()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelTo($user, $names[0]);

        $this->assertFalse($this->service->modelOnAll($user, $names));
    }

    public function test_add_model_to_team_throws_if_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->addModelTo($user, $team->name);
    }

    public function test_add_model_to_team_throws_if_model_does_not_interact()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $team = Team::factory()->create();

        $this->expectException(ModelDoesNotInteractWithTeamsException::class);

        $this->service->addModelTo($model, $team->name);
    }
}
