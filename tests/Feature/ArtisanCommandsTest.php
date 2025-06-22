<?php

namespace Braxey\Gatekeeper\Tests\Feature;

use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ArtisanCommandsTest extends TestCase
{
    public function test_create_permission_command_creates_permission()
    {
        $name = fake()->unique()->word();

        $this->artisan("gatekeeper:create-permission {$name}")
            ->expectsOutput("Permission [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }

    public function test_create_role_command_creates_role_if_enabled()
    {
        Config::set('gatekeeper.features.roles', true);
        $name = fake()->unique()->word();

        $this->artisan("gatekeeper:create-role {$name}")
            ->expectsOutput("Role [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['name' => $name]);
    }

    public function test_create_role_command_throws_if_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Roles feature is disabled.');

        $this->artisan('gatekeeper:create-role '.fake()->unique()->word());
    }

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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Teams feature is disabled.');

        $this->artisan('gatekeeper:create-team '.fake()->unique()->word());
    }
}
