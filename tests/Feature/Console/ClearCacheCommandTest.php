<?php

namespace Braxey\Gatekeeper\Tests\Feature\Console;

use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use Mockery;

class ClearCacheCommandTest extends TestCase
{
    public function test_gatekeeper_cache_clear_command_flushes_all()
    {
        $taggedCache = Mockery::mock(TaggedCache::class);

        Cache::shouldReceive('tags')
            ->with('gatekeeper')
            ->once()
            ->andReturn($taggedCache);

        $taggedCache->shouldReceive('flush')->once();

        $this->artisan('gatekeeper:cache:clear')
            ->expectsOutput('Clearing all Gatekeeper cache (including model-level)...')
            ->expectsOutput('Gatekeeper cache fully cleared!')
            ->assertExitCode(0);
    }
}
