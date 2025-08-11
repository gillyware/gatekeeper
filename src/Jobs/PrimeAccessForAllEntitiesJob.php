<?php

namespace Gillyware\Gatekeeper\Jobs;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PrimeAccessForAllEntitiesJob implements ShouldQueue
{
    use Dispatchable, EnforcesForGatekeeper, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Primes access cache for all role, feature, and team entities.
     */
    public function handle(): void
    {
        if ($this->rolesFeatureEnabled()) {
            resolve(RoleRepository::class)->all()->each(function (Role $role) {
                $this->primeAccessForEntity($role);
            });
        }

        if ($this->featuresFeatureEnabled()) {
            resolve(FeatureRepository::class)->all()->each(function (Feature $feature) {
                $this->primeAccessForEntity($feature);
            });
        }

        if ($this->teamsFeatureEnabled()) {
            resolve(TeamRepository::class)->all()->each(function (Team $team) {
                $this->primeAccessForEntity($team);
            });
        }
    }

    private function primeAccessForEntity(AbstractBaseEntityModel $entityModel): void
    {
        if ($this->modelInteractsWithPermissions($entityModel)) {
            $entityModel->getEffectivePermissions();
        }

        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($entityModel)) {
            $entityModel->getEffectiveRoles();
        }

        if ($this->featuresFeatureEnabled() && $this->modelInteractsWithFeatures($entityModel)) {
            $entityModel->getEffectiveFeatures();
        }

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($entityModel)) {
            $entityModel->getEffectiveTeams();
        }
    }
}
