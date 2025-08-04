<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Jobs\PrimeAccessForAllEntitiesJob;
use Gillyware\Gatekeeper\Jobs\PrimeAccessForModelJob;
use Gillyware\Gatekeeper\Support\AccessCachePrimer;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

class AccessCachePrimerTest extends TestCase
{
    protected AccessCachePrimer $primer;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->primer = new AccessCachePrimer;
    }

    public function test_it_does_not_prime_if_disabled(): void
    {
        Config::set('gatekeeper.cache.prime_model_access.enabled', false);

        $this->primer->primeAccessForAllEntities();
        $this->primer->primeAccessForModel(new User(['id' => 1]));

        Queue::assertNothingPushed();
    }

    public function test_it_dispatches_async_for_all_entities(): void
    {
        Config::set('gatekeeper.cache.prime_model_access.enabled', true);
        Config::set('gatekeeper.cache.prime_model_access.async', true);

        $this->primer->primeAccessForAllEntities();

        Queue::assertPushed(PrimeAccessForAllEntitiesJob::class);
    }

    public function test_it_dispatches_async_for_model(): void
    {
        Config::set('gatekeeper.cache.prime_model_access.enabled', true);
        Config::set('gatekeeper.cache.prime_model_access.async', true);

        $model = new User(['id' => 1]);
        $this->primer->primeAccessForModel($model);

        Queue::assertPushed(function (PrimeAccessForModelJob $job) use ($model) {
            return $job->getModel()->is($model);
        });
    }

    public function test_it_handles_sync_for_all_entities(): void
    {
        Config::set('gatekeeper.cache.prime_model_access.enabled', true);
        Config::set('gatekeeper.cache.prime_model_access.async', false);

        $job = $this->getMockBuilder(PrimeAccessForAllEntitiesJob::class)
            ->onlyMethods(['handle'])
            ->getMock();

        $job->expects($this->once())->method('handle');

        $primer = new class($job) extends AccessCachePrimer
        {
            private PrimeAccessForAllEntitiesJob $mockJob;

            public function __construct($mockJob)
            {
                $this->mockJob = $mockJob;
            }

            protected function makeAllEntitiesJob(): PrimeAccessForAllEntitiesJob
            {
                return $this->mockJob;
            }
        };

        $primer->primeAccessForAllEntities();
    }

    public function test_it_handles_sync_for_model(): void
    {
        Config::set('gatekeeper.cache.prime_model_access.enabled', true);
        Config::set('gatekeeper.cache.prime_model_access.async', false);

        $model = new User(['id' => 1]);

        $job = $this->getMockBuilder(PrimeAccessForModelJob::class)
            ->setConstructorArgs([$model])
            ->onlyMethods(['handle'])
            ->getMock();

        $job->expects($this->once())->method('handle');

        $primer = new class($job) extends AccessCachePrimer
        {
            private PrimeAccessForModelJob $mockJob;

            public function __construct($mockJob)
            {
                $this->mockJob = $mockJob;
            }

            protected function makeModelJob(Model $model): PrimeAccessForModelJob
            {
                return $this->mockJob;
            }
        };

        $primer->primeAccessForModel($model);
    }
}
