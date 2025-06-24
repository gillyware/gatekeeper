<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Exceptions\RolesFeatureDisabledException;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CreateRoleCommandTest extends TestCase
{
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

        $this->expectException(RolesFeatureDisabledException::class);

        $this->artisan('gatekeeper:create-role '.fake()->unique()->word());
    }
}
