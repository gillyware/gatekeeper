<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Exceptions\TeamsFeatureDisabledException;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CreateTeamCommandTest extends TestCase
{
    public function test_create_team_command_creates_team_if_enabled()
    {
        Config::set('gatekeeper.features.teams', true);
        $name = fake()->unique()->word();

        $this->artisan("gatekeeper:create-team {$name}")
            ->expectsOutput("Team [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('teams', ['name' => $name]);
    }

    public function test_create_team_command_throws_if_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $this->artisan('gatekeeper:create-team '.fake()->unique()->word());
    }
}
