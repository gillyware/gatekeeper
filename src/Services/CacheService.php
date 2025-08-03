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
        return $this->cacheRepository->get($this->getModelPermissionLinksCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelPermissionLinks(Model $model, Collection $permissionLinks): void
    {
        $this->cacheRepository->put($this->getModelPermissionLinksCacheKey($model), $permissionLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelPermissionAccess(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelPermissionAccessCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelPermissionAccess(Model $model, Collection $permissionAccess): void
    {
        $this->cacheRepository->put($this->getModelPermissionAccessCacheKey($model), $permissionAccess);
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
    public function invalidateCacheForModelPermissionLinksAndAccess(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelPermissionLinksCacheKey($model));
        $this->cacheRepository->forget($this->getModelPermissionAccessCacheKey($model));
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
        return $this->cacheRepository->get($this->getModelRoleLinksCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelRoleLinks(Model $model, Collection $roleLinks): void
    {
        $this->cacheRepository->put($this->getModelRoleLinksCacheKey($model), $roleLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelRoleAccess(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelRoleAccessCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelRoleAccess(Model $model, Collection $roleAccess): void
    {
        $this->cacheRepository->put($this->getModelRoleAccessCacheKey($model), $roleAccess);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForAllRoles(): void
    {
        $this->cacheRepository->forget($this->getAllRolesCacheKey());
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCacheForModelRoleLinksAndAccess(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelRoleLinksCacheKey($model));
        $this->cacheRepository->forget($this->getModelRoleAccessCacheKey($model));

        $this->invalidateCacheForModelPermissionLinksAndAccess($model);
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
        return $this->cacheRepository->get($this->getModelFeatureLinksCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelFeatureLinks(Model $model, Collection $featureLinks): void
    {
        $this->cacheRepository->put($this->getModelFeatureLinksCacheKey($model), $featureLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelFeatureAccess(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelFeatureAccessCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelFeatureAccess(Model $model, Collection $featureAccess): void
    {
        $this->cacheRepository->put($this->getModelFeatureAccessCacheKey($model), $featureAccess);
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
    public function invalidateCacheForModelFeatureLinksAndAccess(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelFeatureLinksCacheKey($model));
        $this->cacheRepository->forget($this->getModelFeatureAccessCacheKey($model));

        $this->invalidateCacheForModelPermissionLinksAndAccess($model);
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
        return $this->cacheRepository->get($this->getModelTeamLinksCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelTeamLinks(Model $model, Collection $teamLinks): void
    {
        $this->cacheRepository->put($this->getModelTeamLinksCacheKey($model), $teamLinks);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelTeamAccess(Model $model): ?Collection
    {
        return $this->cacheRepository->get($this->getModelTeamAccessCacheKey($model));
    }

    /**
     * {@inheritDoc}
     */
    public function putModelTeamAccess(Model $model, Collection $teamAccess): void
    {
        $this->cacheRepository->put($this->getModelTeamAccessCacheKey($model), $teamAccess);
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
    public function invalidateCacheForModelTeamLinksAndAccess(Model $model): void
    {
        $this->cacheRepository->forget($this->getModelTeamLinksCacheKey($model));
        $this->cacheRepository->forget($this->getModelTeamAccessCacheKey($model));

        $this->invalidateCacheForModelPermissionLinksAndAccess($model);
        $this->invalidateCacheForModelRoleLinksAndAccess($model);
        $this->invalidateCacheForModelFeatureLinksAndAccess($model);
    }

    /**
     * Get the cache key for all permissions.
     */
    private function getAllPermissionsCacheKey(): string
    {
        return 'permissions';
    }

    /**
     * Get the cache key for a specific model's permission links.
     */
    private function getModelPermissionLinksCacheKey(Model $model): string
    {
        return "permissions.{$model->getMorphClass()}.{$model->getKey()}.links";
    }

    /**
     * Get the cache key for a specific model's permission access.
     */
    private function getModelPermissionAccessCacheKey(Model $model): string
    {
        return "permissions.{$model->getMorphClass()}.{$model->getKey()}.access";
    }

    /**
     * Get the cache key for all roles.
     */
    private function getAllRolesCacheKey(): string
    {
        return 'roles';
    }

    /**
     * Get the cache key for a specific model's role links.
     */
    private function getModelRoleLinksCacheKey(Model $model): string
    {
        return "roles.{$model->getMorphClass()}.{$model->getKey()}.links";
    }

    /**
     * Get the cache key for a specific model's role access.
     */
    private function getModelRoleAccessCacheKey(Model $model): string
    {
        return "roles.{$model->getMorphClass()}.{$model->getKey()}.access";
    }

    /**
     * Get the cache key for all features.
     */
    private function getAllFeaturesCacheKey(): string
    {
        return 'features';
    }

    /**
     * Get the cache key for a specific model's feature links.
     */
    private function getModelFeatureLinksCacheKey(Model $model): string
    {
        return "features.{$model->getMorphClass()}.{$model->getKey()}.links";
    }

    /**
     * Get the cache key for a specific model's feature access.
     */
    private function getModelFeatureAccessCacheKey(Model $model): string
    {
        return "features.{$model->getMorphClass()}.{$model->getKey()}.access";
    }

    /**
     * Get the cache key for all teams.
     */
    private function getAllTeamsCacheKey(): string
    {
        return 'teams';
    }

    /**
     * Get the cache key for a specific model's team links.
     */
    private function getModelTeamLinksCacheKey(Model $model): string
    {
        return "teams.{$model->getMorphClass()}.{$model->getKey()}.links";
    }

    /**
     * Get the cache key for a specific model's team access.
     */
    private function getModelTeamAccessCacheKey(Model $model): string
    {
        return "teams.{$model->getMorphClass()}.{$model->getKey()}.access";
    }
}
