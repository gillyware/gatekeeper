<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function addToTeam(Team|string $team): bool
    {
        return Gatekeeper::addModelToTeam($this, $team);
    }

    /**
     * Assign multiple teams to the model.
     */
    public function addToTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::addModelToTeams($this, $teams);
    }

    /**
     * Revoke a team from the model.
     */
    public function removeFromTeam(Team|string $team): bool
    {
        return Gatekeeper::removeModelFromTeam($this, $team);
    }

    /**
     * Revoke multiple teams from the model.
     */
    public function removeFromTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::removeModelFromTeams($this, $teams);
    }

    /**
     * Check if the model has a given team.
     */
    public function onTeam(Team|string $team): bool
    {
        return Gatekeeper::modelOnTeam($this, $team);
    }

    /**
     * Check if the model is on any of the given teams.
     */
    public function onAnyTeam(array|Arrayable $teams): bool
    {
        return Gatekeeper::modelOnAnyTeam($this, $teams);
    }

    /**
     * Check if the model is on all of the given teams.
     */
    public function onAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::modelOnAllTeams($this, $teams);
    }
}
