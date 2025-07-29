<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Repositories\ModelHasFeatureRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class ModelHasFeatureRepositoryTest extends TestCase
{
    protected ModelHasFeatureRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(ModelHasFeatureRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(ModelHasFeatureRepository::class);
    }

    public function test_it_can_check_if_a_feature_is_assigned_to_any_model()
    {
        $feature = Feature::factory()->create();

        $this->assertFalse($this->repository->existsForEntity($feature));

        $user = User::factory()->create();
        $this->repository->assignToModel($user, $feature);

        $this->assertTrue($this->repository->existsForEntity($feature));
    }

    public function test_it_can_create_model_feature_record()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelFeatureLinks')
            ->with($user);

        $record = $this->repository->assignToModel($user, $feature);

        $this->assertInstanceOf(ModelHasFeature::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'feature_id' => $feature->id,
        ]);
    }

    public function test_it_can_soft_delete_model_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelFeatureLinks')
            ->with($user);

        $this->repository->assignToModel($user, $feature);

        $this->assertTrue($this->repository->unassignFromModel($user, $feature));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'feature_id' => $feature->id,
        ]);
    }
}
