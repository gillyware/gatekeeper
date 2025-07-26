<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\CacheServiceInterface;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CacheService implements CacheServiceInterface
{
    public function __construct(private readonly CacheRepository $cacheRepository) {}

    /**
     * Clear the entire Gatekeeper cache.
     */
    public function clear(): void
    {
        $this->cacheRepository->clear();
    }

    /**
     * Retrieve all permissions from the cache.
     */
    public function getAllPermissions(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllPermissionsCacheKey());
    }

    /**
     * Store all permissions in the cache.
     */
    public function putAllPermissions(Collection $permissions): void
    {
        $this->cacheRepository->put($this->getAllPermissionsCacheKey(), $permissions);
    }

    /**
     * Retrieve permission names for a specific model from the cache.
     */
    public function getModelPermissionNames(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelPermissionsCacheKey($model));
    }

    /**
     * Store permission names for a specific model in the cache.
     */
    public function putModelPermissionNames(Model $model, Collection $permissionNames): void
    {
        $this->cacheRepository->put($this->getModelPermissionsCacheKey($model), $permissionNames);
    }

    /**
     * Invalidate the cache for all permissions.
     */
    public function invalidateCacheForAllPermissions(): void
    {
        $this->cacheRepository->forget($this->getAllPermissionsCacheKey());
    }

    /**
     * Invalidate the cache for a specific model's permission names.
     */
    public function invalidateCacheForModelPermissionNames(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelPermissionsCacheKey($model));
    }

    /**
     * Retrieve all roles from the cache.
     */
    public function getAllRoles(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllRolesCacheKey());
    }

    /**
     * Store all roles in the cache.
     */
    public function putAllRoles(Collection $roles): void
    {
        $this->cacheRepository->put($this->getAllRolesCacheKey(), $roles);
    }

    /**
     * Retrieve role names for a specific model from the cache.
     */
    public function getModelRoleNames(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelRolesCacheKey($model));
    }

    /**
     * Store role names for a specific model in the cache.
     */
    public function putModelRoleNames(Model $model, Collection $roleNames): void
    {
        $this->cacheRepository->put($this->getModelRolesCacheKey($model), $roleNames);
    }

    /**
     * Invalidate the cache for all roles.
     */
    public function invalidateCacheForAllRoles(): void
    {
        $this->cacheRepository->forget($this->getAllRolesCacheKey());
    }

    /**
     * Invalidate the cache for a specific model's role names.
     */
    public function invalidateCacheForModelRoleNames(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelRolesCacheKey($model));
    }

    /**
     * Retrieve all features from the cache.
     */
    public function getAllFeatures(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllFeaturesCacheKey());
    }

    /**
     * Store all features in the cache.
     */
    public function putAllFeatures(Collection $features): void
    {
        $this->cacheRepository->put($this->getAllFeaturesCacheKey(), $features);
    }

    /**
     * Retrieve feature names for a specific model from the cache.
     */
    public function getModelFeatureNames(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelFeaturesCacheKey($model));
    }

    /**
     * Store feature names for a specific model in the cache.
     */
    public function putModelFeatureNames(Model $model, Collection $featureNames): void
    {
        $this->cacheRepository->put($this->getModelFeaturesCacheKey($model), $featureNames);
    }

    /**
     * Invalidate the cache for all features.
     */
    public function invalidateCacheForAllFeatures(): void
    {
        $this->cacheRepository->forget($this->getAllFeaturesCacheKey());
    }

    /**
     * Invalidate the cache for a specific model's feature names.
     */
    public function invalidateCacheForModelFeatureNames(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelFeaturesCacheKey($model));
    }

    /**
     * Retrieve all teams from the cache.
     */
    public function getAllTeams(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllTeamsCacheKey());
    }

    /**
     * Store all teams in the cache.
     */
    public function putAllTeams(Collection $teams): void
    {
        $this->cacheRepository->put($this->getAllTeamsCacheKey(), $teams);
    }

    /**
     * Retrieve team names for a specific model from the cache.
     */
    public function getModelTeamNames(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelTeamsCacheKey($model));
    }

    /**
     * Store team names for a specific model in the cache.
     */
    public function putModelTeamNames(Model $model, Collection $teamNames): void
    {
        $this->cacheRepository->put($this->getModelTeamsCacheKey($model), $teamNames);
    }

    /**
     * Invalidate the cache for all teams.
     */
    public function invalidateCacheForAllTeams(): void
    {
        $this->cacheRepository->forget($this->getAllTeamsCacheKey());
    }

    /**
     * Invalidate the cache for a specific model's team names.
     */
    public function invalidateCacheForModelTeamNames(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelTeamsCacheKey($model));
    }

    /**
     * Get the cache key for all permissions.
     */
    private function getAllPermissionsCacheKey(): string
    {
        return 'permissions';
    }

    /**
     * Get the cache key for a specific model's permission names.
     */
    private function getModelPermissionsCacheKey(Model $model): string
    {
        return "permissions.{$model->getMorphClass()}.{$model->getKey()}";
    }

    /**
     * Get the cache key for all roles.
     */
    private function getAllRolesCacheKey(): string
    {
        return 'roles';
    }

    /**
     * Get the cache key for a specific model's role names.
     */
    private function getModelRolesCacheKey(Model $model): string
    {
        return "roles.{$model->getMorphClass()}.{$model->getKey()}";
    }

    /**
     * Get the cache key for all features.
     */
    private function getAllFeaturesCacheKey(): string
    {
        return 'features';
    }

    /**
     * Get the cache key for a specific model's feature names.
     */
    private function getModelFeaturesCacheKey(Model $model): string
    {
        return "features.{$model->getMorphClass()}.{$model->getKey()}";
    }

    /**
     * Get the cache key for all teams.
     */
    private function getAllTeamsCacheKey(): string
    {
        return 'teams';
    }

    /**
     * Get the cache key for a specific model's team names.
     */
    private function getModelTeamsCacheKey(Model $model): string
    {
        return "teams.{$model->getMorphClass()}.{$model->getKey()}";
    }
}
