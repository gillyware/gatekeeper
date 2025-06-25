<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CreateRoleCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit', true);
        Config::set('gatekeeper.features.roles', true);
    }

    public function test_create_role_command_creates_role()
    {
        $name = fake()->unique()->word();
        $actor = User::factory()->create();

        $this->artisan('gatekeeper:create-role', [
            'name' => $name,
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ])->expectsOutput("Role [{$name}] created.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['name' => $name]);
    }

    public function test_create_role_command_throws_if_audit_enabled_but_no_actor()
    {
        $name = fake()->unique()->word();

        $this->artisan('gatekeeper:create-role', [
            'name' => $name,
        ])->expectsOutput('Audit logging is enabled. You must provide --action_by_model_id and --action_by_model_class.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('roles', ['name' => $name]);
    }
}
