<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Models\ModelHasTeam;
use Braxey\Gatekeeper\Models\Team;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function assignTeam(string $teamName): bool
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
     * Revoke a team from the model.
     */
    public function revokeTeam(string $teamName): int
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new \RuntimeException('Cannot revoke teams when the teams feature is disabled.');
        }

        $team = $this->resolveTeamByName($teamName);

        return ModelHasTeam::forModel($this)
            ->where('team_id', $team->id)
            ->whereNull('deleted_at')
            ->delete();
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
     * Get a team by its name.
     *
     * @return \Braxey\Gatekeeper\Models\Team
     */
    private function resolveTeamByName(string $teamName)
    {
        return Team::where('name', $teamName)->firstOrFail();
    }
}
