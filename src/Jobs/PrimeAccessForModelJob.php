<?php

namespace Gillyware\Gatekeeper\Jobs;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PrimeAccessForModelJob implements ShouldQueue
{
    use Dispatchable, EnforcesForGatekeeper, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Model $model) {}

    /**
     * Primes all effective Gatekeeper entities for a specific model.
     * Caches permission, role, feature, and team associations as applicable.
     */
    public function handle(): void
    {
        if ($this->modelInteractsWithPermissions($this->model)) {
            $this->model->getEffectivePermissions();
        }

        if ($this->rolesFeatureEnabled() && $this->modelInteractsWithRoles($this->model)) {
            $this->model->getEffectiveRoles();
        }

        if ($this->featuresFeatureEnabled() && $this->modelInteractsWithFeatures($this->model)) {
            $this->model->getEffectiveFeatures();
        }

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($this->model)) {
            $this->model->getEffectiveTeams();
        }
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
