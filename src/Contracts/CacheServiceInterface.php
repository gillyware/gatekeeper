<?php

namespace Gillyware\Gatekeeper\Contracts;

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
     * @return ?Collection<Permission>
     */
    public function getAllPermissions(): ?Collection;

    /**
     * Store all permissions in the cache.
     */
    public function putAllPermissions(Collection $permissions): void;

    /**
     * Retrieve permission names for a specific model from the cache.
     *
     * @return ?Collection<string>
     */
    public function getModelPermissionNames(Model $model): ?Collection;

    /**
     * Store permission names for a specific model in the cache.
     */
    public function putModelPermissionNames(Model $model, Collection $permissionNames): void;

    /**
     * Invalidate the cache for all permissions.
     */
    public function invalidateCacheForAllPermissions(): void;

    /**
     * Invalidate the cache for a specific model's permission names.
     */
    public function invalidateCacheForModelPermissionNames(Model $model): void;

    /**
     * Retrieve all roles from the cache.
     *
     * @return ?Collection<Role>
     */
    public function getAllRoles(): ?Collection;

    /**
     * Store all roles in the cache.
     */
    public function putAllRoles(Collection $roles): void;

    /**
     * Retrieve role names for a specific model from the cache.
     *
     * @return ?Collection<string>
     */
    public function getModelRoleNames(Model $model): ?Collection;

    /**
     * Store role names for a specific model in the cache.
     */
    public function putModelRoleNames(Model $model, Collection $roleNames): void;

    /**
     * Invalidate the cache for all roles.
     */
    public function invalidateCacheForAllRoles(): void;

    /**
     * Invalidate the cache for a specific model's role names.
     */
    public function invalidateCacheForModelRoleNames(Model $model): void;

    /**
     * Retrieve all teams from the cache.
     *
     * @return ?Collection<Team>
     */
    public function getAllTeams(): ?Collection;

    /**
     * Store all teams in the cache.
     */
    public function putAllTeams(Collection $teams): void;

    /**
     * Retrieve team names for a specific model from the cache.
     *
     * @return ?Collection<string>
     */
    public function getModelTeamNames(Model $model): ?Collection;

    /**
     * Store team names for a specific model in the cache.
     */
    public function putModelTeamNames(Model $model, Collection $teamNames): void;

    /**
     * Invalidate the cache for all teams.
     */
    public function invalidateCacheForAllTeams(): void;

    /**
     * Invalidate the cache for a specific model's team names.
     */
    public function invalidateCacheForModelTeamNames(Model $model): void;
}
