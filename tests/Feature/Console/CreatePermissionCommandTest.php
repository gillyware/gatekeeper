<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class CreatePermissionCommandTest extends TestCase
{
    public function test_permission_is_created_without_audit()
    {
        Config::set('gatekeeper.features.audit', false);

        $exitCode = Artisan::call('gatekeeper:create-permission', [
            'name' => 'example-permission',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('permissions', ['name' => 'example-permission']);
    }

    public function test_permission_is_created_with_audit_and_actor()
    {
        Config::set('gatekeeper.features.audit', true);

        $actor = User::factory()->create();

        $exitCode = Artisan::call('gatekeeper:create-permission', [
            'name' => 'audited-permission',
            '--action_by_model_id' => $actor->id,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('permissions', ['name' => 'audited-permission']);
    }

    public function test_permission_is_created_with_audit_and_fallback_actor()
    {
        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:create-permission', [
            'name' => 'system-actor-permission',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('permissions', ['name' => 'system-actor-permission']);
    }

    public function test_fails_if_actor_class_does_not_exist()
    {
        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:create-permission', [
            'name' => 'fail-permission',
            '--action_by_model_id' => 1,
            '--action_by_model_class' => 'Invalid\\Class',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('permissions', ['name' => 'fail-permission']);
    }

    public function test_fails_if_actor_not_found()
    {
        Config::set('gatekeeper.features.audit', true);

        $exitCode = Artisan::call('gatekeeper:create-permission', [
            'name' => 'fail-permission',
            '--action_by_model_id' => 999,
            '--action_by_model_class' => User::class,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('permissions', ['name' => 'fail-permission']);
    }
}
