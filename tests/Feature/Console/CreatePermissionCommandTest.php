<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\TestCase;

class CreatePermissionCommandTest extends TestCase
{
    public function test_create_permission_command_creates_permission()
    {
        $name = fake()->unique()->word();

        $this->artisan("gatekeeper:create-permission {$name}")
            ->expectsOutput("Permission [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['name' => $name]);
    }
}
