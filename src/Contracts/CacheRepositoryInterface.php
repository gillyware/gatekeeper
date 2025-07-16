<?php

namespace Gillyware\Gatekeeper\Contracts;

interface CacheRepositoryInterface
{
    /**
     * Retrieve a value from the cache.
     */
    public function get(string $key): mixed;

    /**
     * Store a value in the cache.
     */
    public function put(string $key, mixed $value): void;

    /**
     * Remove a value from the cache.
     */
    public function forget(string $key): void;

    /**
     * Clear the entire cache Gatekeeper cache.
     */
    public function clear(): void;
}
