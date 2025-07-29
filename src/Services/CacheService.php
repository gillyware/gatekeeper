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
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->cacheRepository->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPermissions(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllPermissionsCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function putAllPermissions(Collection $permissions): void
    {
        $this->cacheRepository->put($this->getAllPermissionsCacheKey(), $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelPermissionLinks(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelPermissionsCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelPermissionLinks(Model $model, Collection $permissionLinks): void
    {
        $this->cacheRepository->put($this->getModelPermissionsCacheKey($model), $permissionLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForAllPermissions(): void
    {
        $this->cacheRepository->forget($this->getAllPermissionsCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForModelPermissionLinks(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelPermissionsCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function getAllRoles(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllRolesCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function putAllRoles(Collection $roles): void
    {
        $this->cacheRepository->put($this->getAllRolesCacheKey(), $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelRoleLinks(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelRolesCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelRoleLinks(Model $model, Collection $roleLinks): void
    {
        $this->cacheRepository->put($this->getModelRolesCacheKey($model), $roleLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForAllLinks(): void
    {
        $this->cacheRepository->forget($this->getAllRolesCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForModelRoleLinks(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelRolesCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function getAllFeatures(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllFeaturesCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function putAllFeatures(Collection $features): void
    {
        $this->cacheRepository->put($this->getAllFeaturesCacheKey(), $features);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelFeatureLinks(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelFeaturesCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelFeatureLinks(Model $model, Collection $featureLinks): void
    {
        $this->cacheRepository->put($this->getModelFeaturesCacheKey($model), $featureLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForAllFeatures(): void
    {
        $this->cacheRepository->forget($this->getAllFeaturesCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForModelFeatureLinks(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelFeaturesCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTeams(): ?Collection
    {
        return $this->cacheRepository->get($this->getAllTeamsCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function putAllTeams(Collection $teams): void
    {
        $this->cacheRepository->put($this->getAllTeamsCacheKey(), $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelTeamLinks(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelTeamsCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelTeamLinks(Model $model, Collection $teamLinks): void
    {
        $this->cacheRepository->put($this->getModelTeamsCacheKey($model), $teamLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForAllTeams(): void
    {
        $this->cacheRepository->forget($this->getAllTeamsCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForModelTeamLinks(Model $model): void
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
