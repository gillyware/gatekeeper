<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Exceptions\Model\MissingActingAsModelException;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithPermissionsException;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithRolesException;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithTeamsException;
use Gillyware\Gatekeeper\Exceptions\Role\RolesFeatureDisabledException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamsFeatureDisabledException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

trait EnforcesForGatekeeper
{
    use ActsForGatekeeper;

    /**
     * Enforce that the model interacts with permissions.
     */
    protected function enforcePermissionInteraction(Model $model): void
    {
        if (! $this->modelInteractsWithPermissions($model)) {
            throw new ModelDoesNotInteractWithPermissionsException($model);
        }
    }

    /**
     * Check if the model interacts with permissions.
     */
    protected function modelInteractsWithPermissions(Model|string $model): bool
    {
        return in_array(HasPermissions::class, class_uses_recursive($model));
    }

    /**
     * Enforce that the model interacts with roles.
     */
    protected function enforceRoleInteraction(Model $model): void
    {
        if (! $this->modelInteractsWithRoles($model)) {
            throw new ModelDoesNotInteractWithRolesException($model);
        }
    }

    /**
     * Check if the model interacts with roles.
     */
    protected function modelInteractsWithRoles(Model|string $model): bool
    {
        return in_array(HasRoles::class, class_uses_recursive($model));
    }

    /**
     * Enforce that the model interacts with teams.
     */
    protected function enforceTeamInteraction(Model $model): void
    {
        if (! $this->modelInteractsWithTeams($model)) {
            throw new ModelDoesNotInteractWithTeamsException($model);
        }
    }

    /**
     * Check if the model interacts with teams.
     */
    protected function modelInteractsWithTeams(Model|string $model): bool
    {
        return in_array(HasTeams::class, class_uses_recursive($model));
    }

    /**
     * Enforce that the acting model is set when the audit feature is enabled.
     */
    protected function enforceAuditFeature(): void
    {
        if ($this->auditFeatureEnabled() && (! isset($this->actingAs) || ! $this->actingAs instanceof Model)) {
            throw new MissingActingAsModelException;
        }
    }

    /**
     * Check if the audit feature is enabled.
     */
    protected function auditFeatureEnabled(): bool
    {
        return Config::get('gatekeeper.features.audit.enabled', GatekeeperConfigDefault::FEATURES_AUDIT_ENABLED);
    }

    /**
     * Enforce that the roles feature is enabled.
     */
    protected function enforceRolesFeature(): void
    {
        if (! $this->rolesFeatureEnabled()) {
            throw new RolesFeatureDisabledException;
        }
    }

    /**
     * Check if the roles feature is enabled.
     */
    protected function rolesFeatureEnabled(): bool
    {
        return Config::get('gatekeeper.features.roles.enabled', GatekeeperConfigDefault::FEATURES_ROLES_ENABLED);
    }

    /**
     * Enforce that the teams feature is enabled.
     */
    protected function enforceTeamsFeature(): void
    {
        if (! $this->teamsFeatureEnabled()) {
            throw new TeamsFeatureDisabledException;
        }
    }

    /**
     * Check if the teams feature is enabled.
     */
    protected function teamsFeatureEnabled(): bool
    {
        return Config::get('gatekeeper.features.teams.enabled', GatekeeperConfigDefault::FEATURES_TEAMS_ENABLED);
    }
}
