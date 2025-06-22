<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CacheRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.cache.prefix', 'gatekeeper');
        Config::set('gatekeeper.cache.ttl', 600);
    }

    public function test_get_returns_from_local_cache_if_available()
    {
        $repo = new CacheRepository;

        $ref = new \ReflectionClass($repo);
        $prop = $ref->getProperty('localCache');
        $prop->setAccessible(true);
        $cacheKey = 'gatekeeper.1.permissions';
        $prop->setValue($repo, [$cacheKey => ['foo' => 'bar']]);

        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->andReturn(1);

        $result = $repo->get('permissions');

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function test_get_fetches_from_cache_and_stores_locally()
    {
        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->once()
            ->andReturn(1);

        Cache::shouldReceive('get')
            ->with('gatekeeper.1.permissions')
            ->once()
            ->andReturn(['bar' => 'baz']);

        $repo = new CacheRepository;
        $result = $repo->get('permissions');

        $this->assertEquals(['bar' => 'baz'], $result);
    }

    public function test_get_returns_null_if_not_cached()
    {
        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->once()
            ->andReturn(1);

        Cache::shouldReceive('get')
            ->with('gatekeeper.1.unknown')
            ->once()
            ->andReturn(null);

        $repo = new CacheRepository;
        $result = $repo->get('unknown');

        $this->assertNull($result);
    }

    public function test_put_stores_to_cache_and_local_cache()
    {
        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->andReturn(1);

        Cache::shouldReceive('put')
            ->once()
            ->with('gatekeeper.1.key', 'value', 600);

        $repo = new CacheRepository;
        $repo->put('key', 'value');

        $ref = new \ReflectionClass($repo);
        $prop = $ref->getProperty('localCache');
        $prop->setAccessible(true);
        $local = $prop->getValue($repo);

        $this->assertEquals('value', $local['gatekeeper.1.key']);
    }

    public function test_forget_removes_from_cache_and_local_cache()
    {
        Cache::shouldReceive('get')
            ->with('gatekeeper.cache.version')
            ->andReturn(1);

        Cache::shouldReceive('forget')
            ->once()
            ->with('gatekeeper.1.key');

        $repo = new CacheRepository;

        $ref = new \ReflectionClass($repo);
        $prop = $ref->getProperty('localCache');
        $prop->setAccessible(true);
        $prop->setValue($repo, ['gatekeeper.1.key' => 'to-be-removed']);

        $repo->forget('key');

        $this->assertArrayNotHasKey('gatekeeper.1.key', $prop->getValue($repo));
    }

    public function test_clear_increments_cache_version()
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('gatekeeper.cache.version')
            ->andReturn(5);

        Cache::shouldReceive('put')
            ->once()
            ->with('gatekeeper.cache.version', 6, 600);

        $repo = new CacheRepository;
        $repo->clear();
    }

    public function test_get_cache_version_initializes_if_missing()
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('gatekeeper.cache.version')
            ->andReturn(null);

        Cache::shouldReceive('put')
            ->once()
            ->with('gatekeeper.cache.version', 1, 600);

        $repo = new CacheRepository;

        $ref = new \ReflectionClass($repo);
        $method = $ref->getMethod('getCacheVersion');
        $method->setAccessible(true);

        $version = $method->invoke($repo);

        $this->assertEquals(1, $version);
    }
}
