<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function addToTeam(string $teamName): bool
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new \RuntimeException('Cannot assign teams when the teams feature is disabled.');
        }

        $team = $this->resolveTeamByName($teamName);

        $alreadyAssigned = ModelHasTeam::forModel($this)
            ->where('team_id', $team->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyAssigned) {
            return true;
        }

        $modelHasTeam = new ModelHasTeam([
            'team_id' => $team->id,
            'model_type' => $this->getMorphClass(),
            'model_id' => $this->getKey(),
        ]);

        return $modelHasTeam->save();
    }

    /**
     * Assign multiple teams to the model.
     */
    public function addToTeams(array|Arrayable $teamNames): bool
    {
        $result = true;

        foreach (Arr::from($teamNames) as $teamName) {
            $result = $result && $this->addToTeam($teamName);
        }

        return $result;
    }

    /**
     * Revoke a team from the model.
     */
    public function removeFromTeam(string $teamName): bool
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new \RuntimeException('Cannot revoke teams when the teams feature is disabled.');
        }

        $team = $this->resolveTeamByName($teamName);

        ModelHasTeam::forModel($this)
            ->where('team_id', $team->id)
            ->whereNull('deleted_at')
            ->delete();

        return true;
    }

    /**
     * Revoke multiple teams from the model.
     */
    public function removeFromTeams(array|Arrayable $teamNames): bool
    {
        $result = true;

        foreach (Arr::from($teamNames) as $teamName) {
            $result = $result && $this->removeFromTeam($teamName);
        }

        return $result;
    }

    /**
     * Check if the model has a given team.
     */
    public function onTeam(string $teamName): bool
    {
        if (! config('gatekeeper.features.teams', false)) {
            return false;
        }

        $team = $this->resolveTeamByName($teamName);

        if (! $team->is_active) {
            return false;
        }

        return ModelHasTeam::forModel($this)
            ->where('team_id', $team->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Check if the model is on any of the given teams.
     */
    public function onAnyTeam(array|Arrayable $teamNames): bool
    {
        foreach (Arr::from($teamNames) as $teamName) {
            if ($this->onTeam($teamName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model is on all of the given teams.
     */
    public function onAllTeams(array|Arrayable $teamNames): bool
    {
        foreach (Arr::from($teamNames) as $teamName) {
            if (! $this->onTeam($teamName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a team by its name.
     */
    private function resolveTeamByName(string $teamName): Team
    {
        return Team::where('name', $teamName)->firstOrFail();
    }
}
