<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Mockery;

class ClearCacheCommandTest extends TestCase
{
    public function test_gatekeeper_cache_clear_command_increments_cache_version()
    {
        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->once()
            ->andReturn(1);

        Cache::shouldReceive('put')
            ->with('gatekeeper.cache.version', 2, Mockery::type('int'))
            ->once();

        $this->artisan('gatekeeper:cache:clear')
            ->expectsOutput('Clearing all Gatekeeper cache (including model-level)...')
            ->expectsOutput('Gatekeeper cache fully cleared!')
            ->assertExitCode(0);
    }
}
