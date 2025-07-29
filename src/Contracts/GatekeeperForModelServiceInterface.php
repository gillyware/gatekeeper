<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
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
    public function assignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Assign multiple permissions to a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function assignAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Unassign a permission from a model.
     */
    public function unassignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Unassign multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function unassignAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Deny a permission from a model.
     */
    public function denyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Deny multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function denyAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Undeny a permission from a model.
     */
    public function undenyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Undeny multiple permissions from a model.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function undenyAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Check if a model has the given permission.
     */
    public function hasPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool;

    /**
     * Check if a model has any of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool;

    /**
     * Check if a model has all of the given permissions.
     *
     * @param  array<Permission|PermissionPacket|string|UnitEnum>|Arrayable<Permission|PermissionPacket|string|UnitEnum>  $permissions
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool;

    /**
     * Get all permissions assigned directly to a model.
     *
     * @return Collection<string, PermissionPacket>
     */
    public function getDirectPermissions(): Collection;

    /**
     * Get all permissions assigned directly or indirectly to a model.
     *
     * @return Collection<string, PermissionPacket>
     */
    public function getEffectivePermissions(): Collection;

    /**
     * Get all effective permissions for the given model with the permission source(s).
     */
    public function getVerbosePermissions(): Collection;

    /**
     * Assign a role to a model.
     */
    public function assignRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Assign multiple roles to a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function assignAllRoles(array|Arrayable $roles): bool;

    /**
     * Unassign a role from a model.
     */
    public function unassignRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Unassign multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function unassignAllRoles(array|Arrayable $roles): bool;

    /**
     * Deny a role from a model.
     */
    public function denyRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Deny multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function denyAllRoles(array|Arrayable $roles): bool;

    /**
     * Undeny a role from a model.
     */
    public function undenyRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Undeny multiple roles from a model.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function undenyAllRoles(array|Arrayable $roles): bool;

    /**
     * Check if a model has the given role.
     */
    public function hasRole(Role|RolePacket|string|UnitEnum $role): bool;

    /**
     * Check if a model has any of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function hasAnyRole(array|Arrayable $roles): bool;

    /**
     * Check if a model has all of the given roles.
     *
     * @param  array<Role|RolePacket|string|UnitEnum>|Arrayable<Role|RolePacket|string|UnitEnum>  $roles
     */
    public function hasAllRoles(array|Arrayable $roles): bool;

    /**
     * Get all roles assigned directly to a model.
     *
     * @return Collection<string, RolePacket>
     */
    public function getDirectRoles(): Collection;

    /**
     * Get all roles assigned directly or indirectly to a model.
     *
     * @return Collection<string, RolePacket>
     */
    public function getEffectiveRoles(): Collection;

    /**
     * Get all effective roles for the given model with the role source(s).
     */
    public function getVerboseRoles(): Collection;

    /**
     * Assign a feature to a model.
     */
    public function assignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Assign multiple features to a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function assignAllFeatures(array|Arrayable $features): bool;

    /**
     * Unassign a feature from a model.
     */
    public function unassignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Unassign multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function unassignAllFeatures(array|Arrayable $features): bool;

    /**
     * Deny a feature from a model.
     */
    public function denyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Deny multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $roles
     */
    public function denyAllFeatures(array|Arrayable $features): bool;

    /**
     * Undeny a feature from a model.
     */
    public function undenyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Undeny multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $roles
     */
    public function undenyAllFeatures(array|Arrayable $features): bool;

    /**
     * Check if a model has the given feature.
     */
    public function hasFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool;

    /**
     * Check if a model has any of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function hasAnyFeature(array|Arrayable $features): bool;

    /**
     * Check if a model has all of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function hasAllFeatures(array|Arrayable $features): bool;

    /**
     * Get all features assigned directly to a model.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getDirectFeatures(): Collection;

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getEffectiveFeatures(): Collection;

    /**
     * Get all effective features for the given model with the feature source(s).
     */
    public function getVerboseFeatures(): Collection;

    /**
     * Add a model to a team.
     */
    public function addToTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Add a model to multiple teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function addToAllTeams(array|Arrayable $teams): bool;

    /**
     * Remove a model from a team.
     */
    public function removeFromTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Remove a model from multiple teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function removeFromAllTeams(array|Arrayable $teams): bool;

    /**
     * Deny a team from a model.
     */
    public function denyTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Deny multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function denyAllTeams(array|Arrayable $teams): bool;

    /**
     * Undeny a team from a model.
     */
    public function undenyTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Undeny multiple teams from a model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function undenyAllTeams(array|Arrayable $teams): bool;

    /**
     * Check if a model is on a given team.
     */
    public function onTeam(Team|TeamPacket|string|UnitEnum $team): bool;

    /**
     * Check if a model is on any of the specified teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function onAnyTeam(array|Arrayable $teams): bool;

    /**
     * Check if a model is on all of the specified teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function onAllTeams(array|Arrayable $teams): bool;

    /**
     * Get all teams assigned directly to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getDirectTeams(): Collection;

    /**
     * Get all teams assigned directly or indirectly to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getEffectiveTeams(): Collection;

    /**
     * Get all effective teams for the given model with the team source(s).
     */
    public function getVerboseTeams(): Collection;
}
