<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;
use UnitEnum;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function addToTeam(Team|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->addToTeam($team);
    }

    /**
     * Assign multiple teams to the model.
     */
    public function addToAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->addToAllTeams($teams);
    }

    /**
     * Revoke a team from the model.
     */
    public function removeFromTeam(Team|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->removeFromTeam($team);
    }

    /**
     * Revoke multiple teams from the model.
     */
    public function removeFromAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->removeFromAllTeams($teams);
    }

    /**
     * Check if the model has a given team.
     */
    public function onTeam(Team|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->onTeam($team);
    }

    /**
     * Check if the model is on any of the given teams.
     */
    public function onAnyTeam(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->onAnyTeam($teams);
    }

    /**
     * Check if the model is on all of the given teams.
     */
    public function onAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->onAllTeams($teams);
    }
}
