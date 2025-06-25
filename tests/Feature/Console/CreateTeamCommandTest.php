<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CreateTeamCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit', true);
        Config::set('gatekeeper.features.teams', true);
    }

    public function test_create_team_command_creates_team()
    {
        $name = fake()->unique()->word();
        $actor = User::factory()->create();

        $this->artisan('gatekeeper:create-team', [
            'name' => $name,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ])->expectsOutput("Team [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('teams', ['name' => $name]);
    }

    public function test_create_team_command_throws_if_audit_enabled_but_no_actor()
    {
        $name = fake()->unique()->word();

        $this->artisan('gatekeeper:create-team', [
            'name' => $name,
        ])->expectsOutput('Audit logging is enabled. You must provide --action_by_model_id and --action_by_model_class.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('teams', ['name' => $name]);
    }
}
