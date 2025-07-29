<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\GatekeeperForModelServiceInterface;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

class GatekeeperForModelService implements GatekeeperForModelServiceInterface
{
    private Model $model;

    /**
     * {@inheritDoc}
     */
    public function setModel(Model $model): GatekeeperForModelService
    {
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function assignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::assignPermissionToModel($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::assignAllPermissionsToModel($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::unassignPermissionFromModel($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::unassignAllPermissionsFromModel($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function denyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::denyPermissionFromModel($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::denyAllPermissionsFromModel($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::undenyPermissionFromModel($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::undenyAllPermissionsFromModel($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::modelHasPermission($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyPermission(array|Arrayable $permissions): bool
    {
        return Gatekeeper::modelHasAnyPermission($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::modelHasAllPermissions($this->model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectPermissions(): Collection
    {
        return Gatekeeper::getDirectPermissionsForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectivePermissions(): Collection
    {
        return Gatekeeper::getEffectivePermissionsForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosePermissions(): Collection
    {
        return Gatekeeper::getVerbosePermissionsForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function assignRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::assignRoleToModel($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::assignAllRolesToModel($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::unassignRoleFromModel($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::unassignAllRolesFromModel($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function denyRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::denyRoleFromModel($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::denyAllRolesFromModel($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::undenyRoleFromModel($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::undenyAllRolesFromModel($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::modelHasRole($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyRole(array|Arrayable $roles): bool
    {
        return Gatekeeper::modelHasAnyRole($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::modelHasAllRoles($this->model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectRoles(): Collection
    {
        return Gatekeeper::getDirectRolesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveRoles(): Collection
    {
        return Gatekeeper::getEffectiveRolesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseRoles(): Collection
    {
        return Gatekeeper::getVerboseRolesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function assignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::assignFeatureForModel($this->model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::assignAllFeaturesForModel($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::unassignFeatureForModel($this->model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::unassignAllFeaturesForModel($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function denyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::denyFeatureFromModel($this->model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::denyAllFeaturesFromModel($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::undenyFeatureFromModel($this->model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::undenyAllFeaturesFromModel($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function hasFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return Gatekeeper::modelHasFeature($this->model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyFeature(array|Arrayable $features): bool
    {
        return Gatekeeper::modelHasAnyFeature($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAllFeatures(array|Arrayable $features): bool
    {
        return Gatekeeper::modelHasAllFeatures($this->model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectFeatures(): Collection
    {
        return Gatekeeper::getDirectFeaturesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveFeatures(): Collection
    {
        return Gatekeeper::getEffectiveFeaturesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseFeatures(): Collection
    {
        return Gatekeeper::getVerboseFeaturesForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function addToTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::addModelToTeam($this->model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function addToAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::addModelToAllTeams($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::removeModelFromTeam($this->model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::removeModelFromAllTeams($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function denyTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::denyTeamFromModel($this->model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::denyAllTeamsFromModel($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::undenyTeamFromModel($this->model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::undenyAllTeamsFromModel($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function onTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::modelOnTeam($this->model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function onAnyTeam(array|Arrayable $teams): bool
    {
        return Gatekeeper::modelOnAnyTeam($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function onAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::modelOnAllTeams($this->model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectTeams(): Collection
    {
        return Gatekeeper::getDirectTeamsForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveTeams(): Collection
    {
        return Gatekeeper::getEffectiveTeamsForModel($this->model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseTeams(): Collection
    {
        return Gatekeeper::getVerboseTeamsForModel($this->model);
    }
}
