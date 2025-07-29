<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\GatekeeperServiceInterface;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Traits\ActsForGatekeeper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class GatekeeperService implements GatekeeperServiceInterface
{
    use ActsForGatekeeper;

    private string $lifecycleId;

    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RoleService $roleService,
        private readonly FeatureService $featureService,
        private readonly TeamService $teamService,
        private readonly GatekeeperForModelService $gatekeeperForModelService,
    ) {
        $this->setLifecycleId();
    }

    /**
     * {@inheritDoc}
     */
    public function getActor(): ?Model
    {
        $this->resolveActingAs();

        return $this->actingAs;
    }

    /**
     * {@inheritDoc}
     */
    public function getLifecycleId(): string
    {
        return $this->lifecycleId;
    }

    /**
     * {@inheritDoc}
     */
    public function setActor(Model $model): GatekeeperService
    {
        $this->actingAs($model);
        $this->propagateActor($model);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function systemActor(): GatekeeperService
    {
        return $this->setActor(new SystemActor);
    }

    /**
     * {@inheritDoc}
     */
    public function for(Model $model): GatekeeperForModelService
    {
        return $this->gatekeeperForModelService->setModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function permissionExists(string|UnitEnum $permissionName): bool
    {
        return $this->permissionService->exists($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function createPermission(string|UnitEnum $permissionName): PermissionPacket
    {
        return $this->permissionService->create($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function updatePermissionName(Permission|PermissionPacket|string|UnitEnum $permission, string|UnitEnum $permissionName): PermissionPacket
    {
        return $this->permissionService->updateName($permission, $permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function grantPermissionByDefault(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket
    {
        return $this->permissionService->grantByDefault($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function revokePermissionDefaultGrant(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket
    {
        return $this->permissionService->revokeDefaultGrant($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket
    {
        return $this->permissionService->deactivate($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivatePermission(Permission|PermissionPacket|string|UnitEnum $permission): PermissionPacket
    {
        return $this->permissionService->reactivate($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function deletePermission(Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->delete($permission);
    }

    /**
     * {@inheritDoc}
     */
    public function assignPermissionToModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->assignToModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllPermissionsToModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->assignAllToModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->unassignFromModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->unassignAllFromModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function denyPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->denyFromModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->denyAllFromModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyPermissionFromModel(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->undenyFromModel($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllPermissionsFromModel(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->undenyAllFromModel($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasPermission(Model $model, Permission|PermissionPacket|string|UnitEnum $permission): bool
    {
        return $this->permissionService->modelHas($model, $permission);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAnyPermission(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAny($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAllPermissions(Model $model, array|Arrayable $permissions): bool
    {
        return $this->permissionService->modelHasAll($model, $permissions);
    }

    /**
     * {@inheritDoc}
     */
    public function findPermissionByName(string|UnitEnum $permissionName): ?PermissionPacket
    {
        return $this->permissionService->findByName($permissionName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissionService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectPermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectivePermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosePermissionsForModel(Model $model): Collection
    {
        return $this->permissionService->getVerboseForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function roleExists(string|UnitEnum $roleName): bool
    {
        return $this->roleService->exists($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function createRole(string|UnitEnum $roleName): RolePacket
    {
        return $this->roleService->create($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function updateRoleName(Role|RolePacket|string|UnitEnum $role, string|UnitEnum $roleName): RolePacket
    {
        return $this->roleService->updateName($role, $roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function grantRoleByDefault(Role|RolePacket|string|UnitEnum $role): RolePacket
    {
        return $this->roleService->grantByDefault($role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRoleDefaultGrant(Role|RolePacket|string|UnitEnum $role): RolePacket
    {
        return $this->roleService->revokeDefaultGrant($role);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateRole(Role|RolePacket|string|UnitEnum $role): RolePacket
    {
        return $this->roleService->deactivate($role);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivateRole(Role|RolePacket|string|UnitEnum $role): RolePacket
    {
        return $this->roleService->reactivate($role);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteRole(Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->delete($role);
    }

    /**
     * {@inheritDoc}
     */
    public function assignRoleToModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->assignToModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllRolesToModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->assignAllToModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->unassignFromModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->unassignAllFromModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function denyRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->denyFromModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->denyAllFromModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyRoleFromModel(Model $model, Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->undenyFromModel($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllRolesFromModel(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->undenyAllFromModel($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasRole(Model $model, Role|RolePacket|string|UnitEnum $role): bool
    {
        return $this->roleService->modelHas($model, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAnyRole(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAny($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAllRoles(Model $model, array|Arrayable $roles): bool
    {
        return $this->roleService->modelHasAll($model, $roles);
    }

    /**
     * {@inheritDoc}
     */
    public function findRoleByName(string|UnitEnum $roleName): ?RolePacket
    {
        return $this->roleService->findByName($roleName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllRoles(): Collection
    {
        return $this->roleService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectRolesForModel(Model $model): Collection
    {
        return $this->roleService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveRolesForModel(Model $model): Collection
    {
        return $this->roleService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseRolesForModel(Model $model): Collection
    {
        return $this->roleService->getVerboseForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function featureExists(string|UnitEnum $featureName): bool
    {
        return $this->featureService->exists($featureName);
    }

    /**
     * {@inheritDoc}
     */
    public function createFeature(string|UnitEnum $featureName): FeaturePacket
    {
        return $this->featureService->create($featureName);
    }

    /**
     * {@inheritDoc}
     */
    public function updateFeatureName(Feature|FeaturePacket|string|UnitEnum $feature, string|UnitEnum $featureName): FeaturePacket
    {
        return $this->featureService->updateName($feature, $featureName);
    }

    /**
     * {@inheritDoc}
     */
    public function grantFeatureByDefault(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket
    {
        return $this->featureService->grantByDefault($feature);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeFeatureDefaultGrant(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket
    {
        return $this->featureService->revokeDefaultGrant($feature);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket
    {
        return $this->featureService->deactivate($feature);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivateFeature(Feature|FeaturePacket|string|UnitEnum $feature): FeaturePacket
    {
        return $this->featureService->reactivate($feature);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFeature(Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->delete($feature);
    }

    /**
     * {@inheritDoc}
     */
    public function assignFeatureForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->assignToModel($model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function assignAllFeaturesForModel(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->assignAllToModel($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignFeatureForModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->unassignFromModel($model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function unassignAllFeaturesForModel(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->unassignAllFromModel($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function denyFeatureFromModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->denyFromModel($model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllFeaturesFromModel(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->denyAllFromModel($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyFeatureFromModel(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->undenyFromModel($model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllFeaturesFromModel(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->undenyAllFromModel($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasFeature(Model $model, Feature|FeaturePacket|string|UnitEnum $feature): bool
    {
        return $this->featureService->modelHas($model, $feature);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAnyFeature(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->modelHasAny($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function modelHasAllFeatures(Model $model, array|Arrayable $features): bool
    {
        return $this->featureService->modelHasAll($model, $features);
    }

    /**
     * {@inheritDoc}
     */
    public function findFeatureByName(string|UnitEnum $featureName): ?FeaturePacket
    {
        return $this->featureService->findByName($featureName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllFeatures(): Collection
    {
        return $this->featureService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectFeaturesForModel(Model $model): Collection
    {
        return $this->featureService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveFeaturesForModel(Model $model): Collection
    {
        return $this->featureService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseFeaturesForModel(Model $model): Collection
    {
        return $this->featureService->getVerboseForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function teamExists(string|UnitEnum $teamName): bool
    {
        return $this->teamService->exists($teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function createTeam(string|UnitEnum $teamName): TeamPacket
    {
        return $this->teamService->create($teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTeamName(Team|TeamPacket|string|UnitEnum $team, string|UnitEnum $teamName): TeamPacket
    {
        return $this->teamService->updateName($team, $teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function grantTeamByDefault(Team|TeamPacket|string|UnitEnum $team): TeamPacket
    {
        return $this->teamService->grantByDefault($team);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeTeamDefaultGrant(Team|TeamPacket|string|UnitEnum $team): TeamPacket
    {
        return $this->teamService->revokeDefaultGrant($team);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateTeam(Team|TeamPacket|string|UnitEnum $team): TeamPacket
    {
        return $this->teamService->deactivate($team);
    }

    /**
     * {@inheritDoc}
     */
    public function reactivateTeam(Team|TeamPacket|string|UnitEnum $team): TeamPacket
    {
        return $this->teamService->reactivate($team);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->delete($team);
    }

    /**
     * {@inheritDoc}
     */
    public function addModelToTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->assignToModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function addModelToAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->assignAllToModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function removeModelFromTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->unassignFromModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function removeModelFromAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->unassignAllFromModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function denyTeamFromModel(Model $model, Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->denyFromModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function denyAllTeamsFromModel(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->denyAllFromModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyTeamFromModel(Model $model, Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->undenyFromModel($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function undenyAllTeamsFromModel(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->undenyAllFromModel($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnTeam(Model $model, Team|TeamPacket|string|UnitEnum $team): bool
    {
        return $this->teamService->modelHas($model, $team);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnAnyTeam(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelHasAny($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function modelOnAllTeams(Model $model, array|Arrayable $teams): bool
    {
        return $this->teamService->modelHasAll($model, $teams);
    }

    /**
     * {@inheritDoc}
     */
    public function findTeamByName(string|UnitEnum $teamName): ?TeamPacket
    {
        return $this->teamService->findByName($teamName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTeams(): Collection
    {
        return $this->teamService->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectTeamsForModel(Model $model): Collection
    {
        return $this->teamService->getDirectForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEffectiveTeamsForModel(Model $model): Collection
    {
        return $this->teamService->getForModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerboseTeamsForModel(Model $model): Collection
    {
        return $this->teamService->getVerboseForModel($model);
    }

    /**
     * Set the lifecycle ID for the current request or CLI execution.
     */
    private function setLifecycleId(): void
    {
        $prefix = app()->runningInConsole() ? 'cli_' : 'request_';
        $this->lifecycleId = $prefix.Str::uuid()->toString();
    }

    /**
     * Propagate the acting as model to the services.
     */
    private function propagateActor(Model $model): void
    {
        $this->permissionService->actingAs($model);
        $this->roleService->actingAs($model);
        $this->featureService->actingAs($model);
        $this->teamService->actingAs($model);
    }
}
