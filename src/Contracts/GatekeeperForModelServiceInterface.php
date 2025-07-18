<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

interface GatekeeperForModelServiceInterface
{
    /**
     * Set the model being acted on.
     */
    public function setModel(Model $model): GatekeeperForModelService;

    /**
     * Assign a permission to a model.
     */
    public function assignPermission(Permission|string|UnitEnum $permission): bool;

    /**
     * Assign multiple permissions to a model.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function assignAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Revoke a permission from a model.
     */
    public function revokePermission(Permission|string|UnitEnum $permission): bool;

    /**
     * Revoke multiple permissions from a model.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function revokeAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Check if a model has the given permission.
     */
    public function hasPermission(Permission|string|UnitEnum $permission): bool;

    /**
     * Check if a model has any of the given permissions.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool;

    /**
     * Check if a model has all of the given permissions.
     *
     * @param  array<Permission|string|UnitEnum>|Arrayable<Permission|string|UnitEnum>  $permissions
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<Permission>
     */
    public function getDirectPermissions(): Collection;

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<Permission>
     */
    public function getEffectivePermissions(): Collection;

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerbosePermissions(): Collection;

    /**
     * Assign a role to a model.
     */
    public function assignRole(Role|string|UnitEnum $role): bool;

    /**
     * Assign multiple roles to a model.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function assignAllRoles(array|Arrayable $roles): bool;

    /**
     * Revoke a role from a model.
     */
    public function revokeRole(Role|string|UnitEnum $role): bool;

    /**
     * Revoke multiple roles from a model.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function revokeAllRoles(array|Arrayable $roles): bool;

    /**
     * Check if a model has the given role.
     */
    public function hasRole(Role|string|UnitEnum $role): bool;

    /**
     * Check if a model has any of the given roles.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function hasAnyRole(array|Arrayable $roles): bool;

    /**
     * Check if a model has all of the given roles.
     *
     * @param  array<Role|string|UnitEnum>|Arrayable<Role|string|UnitEnum>  $roles
     */
    public function hasAllRoles(array|Arrayable $roles): bool;

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<Role>
     */
    public function getDirectRoles(): Collection;

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<Role>
     */
    public function getEffectiveRoles(): Collection;

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseRoles(): Collection;

    /**
     * Add a model to a team.
     */
    public function addToTeam(Team|string|UnitEnum $team): bool;

    /**
     * Add a model to multiple teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function addToAllTeams(array|Arrayable $teams): bool;

    /**
     * Remove a model from a team.
     */
    public function removeFromTeam(Team|string|UnitEnum $team): bool;

    /**
     * Remove a model from multiple teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function removeFromAllTeams(array|Arrayable $teams): bool;

    /**
     * Check if a model is on a given team.
     */
    public function onTeam(Team|string|UnitEnum $team): bool;

    /**
     * Check if a model is on any of the specified teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function onAnyTeam(array|Arrayable $teams): bool;

    /**
     * Check if a model is on all of the specified teams.
     *
     * @param  array<Team|string|UnitEnum>|Arrayable<Team|string|UnitEnum>  $teams
     */
    public function onAllTeams(array|Arrayable $teams): bool;

    /**
     * Get all teams assigned to a model.
     *
     * @return Collection<Team>
     */
    public function getTeams(): Collection;
}
