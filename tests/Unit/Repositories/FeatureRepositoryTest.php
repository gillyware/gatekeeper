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

    public function test_update_feature_updates_name_and_clears_cache()
    {
        $feature = Feature::factory()->create();
        $newName = fake()->unique()->word();

        $this->cacheService->expects($this->once())->method('clear');

        $updatedFeature = $this->repository->update($feature, $newName);

        $this->assertEquals($newName, $updatedFeature->name);
    }

    public function test_turn_feature_off_by_default_sets_flag_and_clears_cache()
    {
        $feature = Feature::factory()->create(['default_enabled' => true]);

        $this->cacheService->expects($this->once())->method('clear');

        $defaultOffFeature = $this->repository->turnOffByDefault($feature);

        $this->assertFalse($defaultOffFeature->default_enabled);
    }

    public function test_turn_feature_on_by_default_sets_flag_and_clears_cache()
    {
        $feature = Feature::factory()->create(['default_enabled' => false]);

        $this->cacheService->expects($this->once())->method('clear');

        $defaultOnFeature = $this->repository->turnOnByDefault($feature);

        $this->assertTrue($defaultOnFeature->default_enabled);
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

    public function test_active_returns_only_active_features()
    {
        $inactive = Feature::factory()->count(2)->inactive()->create();
        $active = Feature::factory()->count(2)->create();

        $all = $inactive->concat($active);

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn($all);

        $result = $this->repository->active();

        $this->assertEqualsCanonicalizing(
            $active->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_features()
    {
        $features = Feature::factory()->count(3)->create();
        $names = $features->pluck('name');

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn($features);

        $result = $this->repository->whereNameIn($names);

        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $features->pluck('id')->toArray(),
            $result->pluck('id')->toArray()
        );
    }

    public function test_where_name_in_returns_empty_collection_when_no_matches()
    {
        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect());

        $result = $this->repository->whereNameIn(['nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_get_all_feature_names_for_model_caches_result()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $user->features()->attach($feature);

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(null);

        $this->cacheService->expects($this->once())
            ->method('putModelFeatureNames')
            ->with($user, collect([$feature->name]));

        $names = $this->repository->namesForModel($user);

        $this->assertContains($feature->name, $names->toArray());
    }

    public function test_get_all_features_for_model_returns_features()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $user->features()->attach($feature);

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(collect([$feature->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect([$feature->name => $feature]));

        $features = $this->repository->forModel($user);

        $this->assertCount(1, $features);
        $this->assertTrue($features->first()->is($feature));
    }

    public function test_get_all_features_for_model_returns_empty_when_no_features()
    {
        $user = User::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect());

        $features = $this->repository->forModel($user);

        $this->assertCount(0, $features);
    }

    public function test_active_for_model_returns_active_features()
    {
        $user = User::factory()->create();
        $activeFeature = Feature::factory()->create();
        $inactiveFeature = Feature::factory()->inactive()->create();

        $user->features()->attach([$activeFeature->id, $inactiveFeature->id]);

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(collect([$activeFeature->name, $inactiveFeature->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect([$activeFeature, $inactiveFeature]));

        $features = $this->repository->activeForModel($user);

        $this->assertCount(1, $features);
        $this->assertTrue($features->first()->is($activeFeature));
    }

    public function test_find_by_name_for_model_returns_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $user->features()->attach($feature);

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(collect([$feature->name]));

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect([$feature->name => $feature]));

        $result = $this->repository->findByNameForModel($user, $feature->name);
        $this->assertTrue($result->is($feature));
    }

    public function test_find_by_name_for_model_returns_null_if_not_found()
    {
        $user = User::factory()->create();
        $featureName = fake()->unique()->word();

        $this->cacheService->expects($this->once())
            ->method('getModelFeatureNames')
            ->with($user)
            ->willReturn(collect());

        $this->cacheService->expects($this->once())
            ->method('getAllFeatures')
            ->willReturn(collect());

        $result = $this->repository->findByNameForModel($user, $featureName);
        $this->assertNull($result);
    }
}
