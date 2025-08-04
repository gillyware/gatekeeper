<?php

namespace Gillyware\Gatekeeper\Support;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Jobs\PrimeAccessForAllEntitiesJob;
use Gillyware\Gatekeeper\Jobs\PrimeAccessForModelJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AccessCachePrimer
{
    public function primeAccessForAllEntities(): void
    {
        if (! $this->isPrimingEnabled()) {
            return;
        }

        $this->isAsync()
            ? PrimeAccessForAllEntitiesJob::dispatch()
            : $this->makeAllEntitiesJob()->handle();
    }

    public function primeAccessForModel(Model $model): void
    {
        if (! $this->isPrimingEnabled()) {
            return;
        }

        $this->isAsync()
            ? PrimeAccessForModelJob::dispatch($model)
            : $this->makeModelJob($model)->handle();
    }

    protected function makeAllEntitiesJob(): PrimeAccessForAllEntitiesJob
    {
        return new PrimeAccessForAllEntitiesJob;
    }

    protected function makeModelJob(Model $model): PrimeAccessForModelJob
    {
        return new PrimeAccessForModelJob($model);
    }

    protected function isPrimingEnabled(): bool
    {
        return Config::get('gatekeeper.cache.prime_model_access.enabled', GatekeeperConfigDefault::CACHE_PRIME_MODEL_ACCESS_ENABLED);
    }

    protected function isAsync(): bool
    {
        return Config::get('gatekeeper.cache.prime_model_access.async', GatekeeperConfigDefault::CACHE_PRIME_MODEL_ACCESS_ASYNC);
    }
}
