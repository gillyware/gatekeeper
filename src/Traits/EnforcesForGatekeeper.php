<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Exceptions\MissingActingAsModelException;
use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithPermissionsException;
use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithRolesException;
use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithTeamsException;
use Braxey\Gatekeeper\Exceptions\RolesFeatureDisabledException;
use Braxey\Gatekeeper\Exceptions\TeamsFeatureDisabledException;
use Illuminate\Database\Eloquent\Model;

trait EnforcesForGatekeeper
{
    use ActsForGatekeeper;

    /**
     * Enforce that the model interacts with permissions.
     */
    protected function enforcePermissionInteraction(Model $model): void
    {
        if (! in_array(InteractsWithPermissions::class, class_uses_recursive($model))) {
            throw new ModelDoesNotInteractWithPermissionsException($model);
        }
    }

    /**
     * Enforce that the model interacts with roles.
     */
    protected function enforceRoleInteraction(Model $model): void
    {
        if (! in_array(InteractsWithRoles::class, class_uses_recursive($model))) {
            throw new ModelDoesNotInteractWithRolesException($model);
        }
    }

    /**
     * Enforce that the model interacts with teams.
     */
    protected function enforceTeamInteraction(Model $model): void
    {
        if (! in_array(InteractsWithTeams::class, class_uses_recursive($model))) {
            throw new ModelDoesNotInteractWithTeamsException($model);
        }
    }

    /**
     * Enforce that the acting model is set when the audit feature is enabled.
     */
    protected function enforceAuditFeature(): void
    {
        if (config('gatekeeper.features.audit', true) && (! isset($this->actingAs) || ! $this->actingAs instanceof Model)) {
            throw new MissingActingAsModelException;
        }
    }

    /**
     * Enforce that the roles feature is enabled.
     */
    protected function enforceRolesFeature(): void
    {
        if (! config('gatekeeper.features.roles', false)) {
            throw new RolesFeatureDisabledException;
        }
    }

    /**
     * Enforce that the teams feature is enabled.
     */
    protected function enforceTeamsFeature(): void
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new TeamsFeatureDisabledException;
        }
    }
}
