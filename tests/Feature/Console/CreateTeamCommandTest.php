<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class CreateTeamCommandTest extends TestCase
{
    public function test_team_is_created_without_audit()
    {
        Config::set('gatekeeper.features.teams', true);
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:create-team', [
            'name' => 'basic-team',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('teams', ['name' => 'basic-team']);
    }

    public function test_team_is_created_with_audit_and_actor()
    {
        Config::set('gatekeeper.features.audit', true);
        Config::set('gatekeeper.features.teams', true);
        $actor = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:create-team', [
            'name' => 'audited-team',
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('teams', ['name' => 'audited-team']);
    }

    public function test_team_is_created_with_audit_and_fallback_actor()
    {
        Config::set('gatekeeper.features.audit', true);
        Config::set('gatekeeper.features.teams', true);

        $exitCode = Artisan::call('gatekeeper:create-team', [
            'name' => 'system-actor-team',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('teams', ['name' => 'system-actor-team']);
    }

    public function test_fails_if_actor_class_does_not_exist()
    {
        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:create-team', [
            'name' => 'invalid-class-team',
            '--action_by_model_id' => 1,
            '--action_by_model_class' => 'Invalid\\Class',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('teams', ['name' => 'invalid-class-team']);
    }

    public function test_fails_if_actor_not_found()
    {
        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:create-team', [
            'name' => 'missing-actor-team',
            '--action_by_model_id' => 999,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('teams', ['name' => 'missing-actor-team']);
    }
}
