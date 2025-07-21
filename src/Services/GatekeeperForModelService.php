<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\GatekeeperForModelServiceInterface;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\PermissionPacket;
use Gillyware\Gatekeeper\Packets\RolePacket;
use Gillyware\Gatekeeper\Packets\TeamPacket;
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
    public function revokePermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return Gatekeeper::revokePermissionFromModel($this->model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllPermissions(array|Arrayable $permissions): bool
    {
        return Gatekeeper::revokeAllPermissionsFromModel($this->model, $permissions);
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
    public function revokeRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return Gatekeeper::revokeRoleFromModel($this->model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllRoles(array|Arrayable $roles): bool
    {
        return Gatekeeper::revokeAllRolesFromModel($this->model, $roles);
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
    public function getTeams(): Collection
    {
        return Gatekeeper::getTeamsForModel($this->model);
    }
}
