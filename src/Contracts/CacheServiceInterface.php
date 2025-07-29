<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CacheServiceInterface
{
    /**
     * Clear the entire Gatekeeper cache.
     */
    public function clear(): void;

    /**
     * Retrieve all permissions from the cache.
     *
     * @return ?Collection<string, Permission>
     */
    public function getAllPermissions(): ?Collection;

    /**
     * Store all permissions in the cache.
     *
     * @param  ?Collection<string, Permission>  $permissions
     */
    public function putAllPermissions(Collection $permissions): void;

    /**
     * Retrieve permission names for a specific model from the cache.
     *
     * @return ?Collection<int, array{name: string, denied: bool}>
     */
    public function getModelPermissionLinks(Model $model): ?Collection;

    /**
     * Store permission names for a specific model in the cache.
     *
     * @param  ?Collection<int, array{name: string, denied: bool}>  $permissionLinks
     */
    public function putModelPermissionLinks(Model $model, Collection $permissionLinks): void;

    /**
     * Invalidate the cache for all permissions.
     */
    public function invalidateCacheForAllPermissions(): void;

    /**
     * Invalidate the cache for a specific model's permission names.
     */
    public function invalidateCacheForModelPermissionLinks(Model $model): void;

    /**
     * Retrieve all roles from the cache.
     *
     * @return ?Collection<string, Role>
     */
    public function getAllRoles(): ?Collection;

    /**
     * Store all roles in the cache.
     *
     * @param  ?Collection<string, Role>  $roles
     */
    public function putAllRoles(Collection $roles): void;

    /**
     * Retrieve role names for a specific model from the cache.
     *
     * @return ?Collection<int, array{name: string, denied: bool}>
     */
    public function getModelRoleLinks(Model $model): ?Collection;

    /**
     * Store role names for a specific model in the cache.
     *
     * @param  ?Collection<int, array{name: string, denied: bool}>  $roleLinks
     */
    public function putModelRoleLinks(Model $model, Collection $roleLinks): void;

    /**
     * Invalidate the cache for all roles.
     */
    public function invalidateCacheForAllLinks(): void;

    /**
     * Invalidate the cache for a specific model's role names.
     */
    public function invalidateCacheForModelRoleLinks(Model $model): void;

    /**
     * Retrieve all features from the cache.
     *
     * @return ?Collection<string, Feature>
     */
    public function getAllFeatures(): ?Collection;

    /**
     * Store all features in the cache.
     *
     * @param  ?Collection<string, Feature>  $features
     */
    public function putAllFeatures(Collection $features): void;

    /**
     * Retrieve feature names for a specific model from the cache.
     *
     * @return ?Collection<int, array{name: string, denied: bool}>
     */
    public function getModelFeatureLinks(Model $model): ?Collection;

    /**
     * Store feature names for a specific model in the cache.
     *
     * @param  ?Collection<int, array{name: string, denied: bool}>  $featureLinks
     */
    public function putModelFeatureLinks(Model $model, Collection $featureLinks): void;

    /**
     * Invalidate the cache for all features.
     */
    public function invalidateCacheForAllFeatures(): void;

    /**
     * Invalidate the cache for a specific model's feature names.
     */
    public function invalidateCacheForModelFeatureLinks(Model $model): void;

    /**
     * Retrieve all teams from the cache.
     *
     * @return ?Collection<string, Team>
     */
    public function getAllTeams(): ?Collection;

    /**
     * Store all teams in the cache.
     *
     * @param  ?Collection<string, Team>  $teams
     */
    public function putAllTeams(Collection $teams): void;

    /**
     * Retrieve team names for a specific model from the cache.
     *
     * @return ?Collection<int, array{name: string, denied: bool}>
     */
    public function getModelTeamLinks(Model $model): ?Collection;

    /**
     * Store team names for a specific model in the cache.
     *
     * @return ?Collection<int, array{name: string, denied: bool}> $teamLinks
     */
    public function putModelTeamLinks(Model $model, Collection $teamLinks): void;

    /**
     * Invalidate the cache for all teams.
     */
    public function invalidateCacheForAllTeams(): void;

    /**
     * Invalidate the cache for a specific model's team names.
     */
    public function invalidateCacheForModelTeamLinks(Model $model): void;
}
