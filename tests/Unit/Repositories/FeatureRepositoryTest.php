<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Exceptions\Feature\FeatureNotFoundException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FeatureRepositoryTest extends TestCase
{
    protected FeatureRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(FeatureRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(FeatureRepository::class);
    }

    public function test_feature_exists_returns_true_if_exists()
    {
        $feature = Feature::factory()->create();

        $this->assertTrue($this->repository->exists($feature->name));
    }

    public function test_feature_exists_returns_false_if_not_exists()
    {
        $this->assertFalse($this->repository->exists(fake()->unique()->word()));
    }

    public function test_find_by_name_returns_feature_if_exists()
    {
        $feature = Feature::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect([$feature->name => $feature]));

        $result = $this->repository->findByName($feature->name);

        $this->assertTrue($feature->is($result));
    }

    public function test_find_by_name_returns_null_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect());

        $result = $this->repository->findByName(fake()->unique()->word());

        $this->assertNull($result);
    }

    public function test_find_or_fail_by_name_returns_feature_if_exists()
    {
        $feature = Feature::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect([$feature->name => $feature]));

        $result = $this->repository->findOrFailByName($feature->name);

        $this->assertTrue($feature->is($result));
    }

    public function test_find_or_fail_by_name_throws_if_not_exists()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect());

        $this->expectException(FeatureNotFoundException::class);

        $this->repository->findOrFailByName(fake()->unique()->word());
    }

    public function test_create_stores_feature_and_forgets_cache()
    {
        $this->cacheService->expects($this->once())->method('invalidateCacheForAllFeatures');

        $name = fake()->unique()->word();
        $feature = $this->repository->create($name);

        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertTrue($this->repository->exists($name));
    }

    public function test_update_feature_name_updates_name_and_clears_cache()
    {
        $feature = Feature::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedFeature = $this->repository->updateName($feature, $newName);

        $this->assertEquals($newName, $updatedFeature->name);
    }

    public function test_turn_feature_off_by_default_sets_flag_and_clears_cache()
    {
        $feature = Feature::factory()->create(['grant_by_default' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $defaultOffFeature = $this->repository->revokeDefaultGrant($feature);

        $this->assertFalse($defaultOffFeature->grant_by_default);
    }

    public function test_turn_feature_on_by_default_sets_flag_and_clears_cache()
    {
        $feature = Feature::factory()->create(['grant_by_default' => false]);

        $this->cacheService->expects($this->once())->method('clear');

        $grantByDefaultFeature = $this->repository->grantByDefault($feature);

        $this->assertTrue($grantByDefaultFeature->grant_by_default);
    }

    public function test_deactivate_feature_sets_active_to_false_and_clears_cache()
    {
        $feature = Feature::factory()->create(['is_active' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $deactivatedFeature = $this->repository->deactivate($feature);

        $this->assertFalse($deactivatedFeature->is_active);
    }

    public function test_reactivate_feature_sets_active_to_true_and_clears_cache()
    {
        $feature = Feature::factory()->inactive()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $activatedFeature = $this->repository->reactivate($feature);

        $this->assertTrue($activatedFeature->is_active);
    }

    public function test_delete_feature_soft_deletes_and_clears_cache()
    {
        $feature = Feature::factory()->create();

        $this->cacheService->expects($this->once())->method('clear');

        $this->repository->delete($feature);

        $this->assertSoftDeleted($feature->fresh());
    }

    public function test_all_returns_cached_if_available()
    {
        $cached = Feature::factory()->count(2)->make();

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn($cached);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(
            $cached->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_all_caches_result_if_not_cached()
    {
        $features = Feature::factory()->count(3)->create();

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putAllFeatures')
            ->with(Feature::all()->mapWithKeys(fn (Feature $r) => [$r->name => $r]));

        $this->assertEqualsCanonicalizing(
            $features->pluck('id')->toArray(),
            $this->repository->all()->pluck('id')->toArray()
        );
    }

    public function test_get_assigned_features_for_model_caches_result()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $user->features()->attach($feature);

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureLinks')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelFeatureLinks')
            ->with($user, collect([[
                'name' => $feature->name,
                'denied' => 0,
            ]]));

        $features = $this->repository->assignedToModel($user);

        $this->assertEquals($feature->name, $features->first()->name);
    }
}
