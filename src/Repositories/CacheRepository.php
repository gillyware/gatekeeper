<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\CacheRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CacheRepository implements CacheRepositoryInterface
{
    private array $localCache = [];

    private bool $cachingEnabled;

    private string $prefix;

    private int $ttl;

    private int $cacheVersion;

    public function __construct()
    {
        $this->cachingEnabled = (bool) Config::get('gatekeeper.cache.enabled', GatekeeperConfigDefault::CACHE_ENABLED);
        $this->prefix = Config::get('gatekeeper.cache.prefix', GatekeeperConfigDefault::CACHE_PREFIX);
        $this->ttl = (int) Config::get('gatekeeper.cache.ttl', GatekeeperConfigDefault::CACHE_TTL);
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

        if (! $this->cachingEnabled) {
            return null;
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

        if (! $this->cachingEnabled) {
            return;
        }

        Cache::put($cacheKey, $value, $this->ttl);

        $this->trackCacheKey($cacheKey);
    }

    /**
     * Remove a value from the cache.
     */
    public function forget(string $key): void
    {
        $cacheKey = $this->buildCacheKey($key);
        unset($this->localCache[$cacheKey]);

        if (! $this->cachingEnabled) {
            return;
        }

        Cache::forget($cacheKey);
    }

    /**
     * Clear the entire cache Gatekeeper cache.
     */
    public function clear(): void
    {
        $currentCacheVersion = $this->getCacheVersion();

        $cacheKey = "{$this->prefix}.meta.version";
        $newCacheVersion = $currentCacheVersion + 1;

        if (! $this->cachingEnabled) {
            return;
        }

        Cache::put($cacheKey, $newCacheVersion, $this->ttl);

        $this->cacheVersion = $newCacheVersion;

        $this->forgetCacheVersion($currentCacheVersion);
    }

    /**
     * Build a cache key with the prefix and version.
     */
    private function buildCacheKey(string $key, ?int $version = null): string
    {
        $version ??= $this->getCacheVersion();

        return "{$this->prefix}.{$version}.{$key}";
    }

    /**
     * Get the current cache version, or initialize it if not set.
     */
    private function getCacheVersion(): int
    {
        if (isset($this->cacheVersion)) {
            return $this->cacheVersion;
        }

        $cacheKey = "{$this->prefix}.meta.version";
        $cacheVersion = Cache::get($cacheKey);

        if (! $cacheVersion) {
            $cacheVersion = 1;
            Cache::put($cacheKey, $cacheVersion, $this->ttl);
        }

        $this->cacheVersion = $cacheVersion;

        return $cacheVersion;
    }

    /**
     * Track the given cache key so it can be forgotten on cache invalidation.
     */
    private function trackCacheKey(string $key): void
    {
        $allTrackedKeysKey = "{$this->prefix}.meta.tracked_keys";
        $allTrackedKeys = $this->localCache[$allTrackedKeysKey] ?? Cache::get($allTrackedKeysKey, []);

        $currentVersionTrackedKeysKey = $this->buildCacheKey('meta.tracked_keys');
        $currentVersionTrackedKeys = $allTrackedKeys[$currentVersionTrackedKeysKey] ?? [];

        if (in_array($key, $currentVersionTrackedKeys)) {
            return;
        }

        $currentVersionTrackedKeys[] = $key;
        $allTrackedKeys[$currentVersionTrackedKeysKey] = $currentVersionTrackedKeys;

        Cache::put($allTrackedKeysKey, $allTrackedKeys, $this->ttl);
        $this->localCache[$allTrackedKeysKey] = $allTrackedKeys;
    }

    /**
     * Forget all the cache entries for a specific cache version.
     */
    private function forgetCacheVersion(int $version): void
    {
        $allTrackedKeysKey = "{$this->prefix}.meta.tracked_keys";
        $allTrackedKeys = $this->localCache[$allTrackedKeysKey] ?? Cache::get($allTrackedKeysKey, []);

        $versionTrackedKeysKey = $this->buildCacheKey('meta.tracked_keys', $version);
        $versionTrackedKeys = $allTrackedKeys[$versionTrackedKeysKey] ?? [];

        foreach ($versionTrackedKeys as $trackedKey) {
            Cache::forget($trackedKey);
        }

        unset($allTrackedKeys[$versionTrackedKeysKey]);

        Cache::put($allTrackedKeysKey, $allTrackedKeys, $this->ttl);
        $this->localCache[$allTrackedKeysKey] = $allTrackedKeys;
    }
}
