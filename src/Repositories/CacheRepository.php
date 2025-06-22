<?php

namespace Gillyware\Gatekeeper\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CacheRepository
{
    private array $localCache = [];

    private string $prefix;

    private int $ttl;

    public function __construct()
    {
        $this->prefix = Config::get('gatekeeper.cache.prefix');
        $this->ttl = (int) Config::get('gatekeeper.cache.ttl');
    }

    /**
     * Retrieve a value from the cache.
     */
    public function get(string $key): mixed
    {
        $cacheKey = $this->buildCacheKey($key);

        if (isset($this->localCache[$cacheKey])) {
            return $this->localCache[$cacheKey];
        }

        $cachedValue = Cache::get($cacheKey);

        if ($cachedValue !== null) {
            $this->localCache[$cacheKey] = $cachedValue;

            return $cachedValue;
        }

        return null;
    }

    /**
     * Store a value in the cache.
     */
    public function put(string $key, mixed $value): void
    {
        $cacheKey = $this->buildCacheKey($key);
        $this->localCache[$cacheKey] = $value;
        Cache::put($cacheKey, $value, $this->ttl);
    }

    /**
     * Remove a value from the cache.
     */
    public function forget(string $key): void
    {
        $cacheKey = $this->buildCacheKey($key);
        unset($this->localCache[$cacheKey]);
        Cache::forget($cacheKey);
    }

    /**
     * Clear the entire cache by incrementing the cache version.
     */
    public function clear(): void
    {
        $cacheKey = "{$this->prefix}.cache.version";
        $newCacheVersion = $this->getCacheVersion() + 1;
        Cache::put($cacheKey, $newCacheVersion, $this->ttl);
    }

    /**
     * Build a cache key with the prefix and version.
     */
    private function buildCacheKey(string $key): string
    {
        $cacheVersion = $this->getCacheVersion();

        return "{$this->prefix}.{$cacheVersion}.{$key}";
    }

    /**
     * Get the current cache version, or initialize it if not set.
     */
    private function getCacheVersion(): int
    {
        $cacheKey = "{$this->prefix}.cache.version";
        $cacheVersion = Cache::get($cacheKey);

        if (! $cacheVersion) {
            $cacheVersion = 1;
            Cache::put($cacheKey, $cacheVersion, $this->ttl);
        }

        return $cacheVersion;
    }
}
