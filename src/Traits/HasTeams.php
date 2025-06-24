<?php

namespace Braxey\Gatekeeper\Traits;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Models\Team;
use Illuminate\Contracts\Support\Arrayable;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function addToTeam(string $teamName): bool
    {
        return Gatekeeper::addModelToTeam($this, $teamName);
    }

    /**
     * Assign multiple teams to the model.
     */
    public function addToTeams(array|Arrayable $teamNames): bool
    {
        return Gatekeeper::addModelToTeams($this, $teamNames);
    }

    /**
     * Revoke a team from the model.
     */
    public function removeFromTeam(string $teamName): bool
    {
        return Gatekeeper::removeModelFromTeam($this, $teamName);
    }

    /**
     * Revoke multiple teams from the model.
     */
    public function removeFromTeams(array|Arrayable $teamNames): bool
    {
        return Gatekeeper::removeModelFromTeams($this, $teamNames);
    }

    /**
     * Check if the model has a given team.
     */
    public function onTeam(string $teamName): bool
    {
        return Gatekeeper::modelOnTeam($this, $teamName);
    }

    /**
     * Check if the model is on any of the given teams.
     */
    public function onAnyTeam(array|Arrayable $teamNames): bool
    {
        return Gatekeeper::modelOnAnyTeam($this, $teamNames);
    }

    /**
     * Check if the model is on all of the given teams.
     */
    public function onAllTeams(array|Arrayable $teamNames): bool
    {
        return Gatekeeper::modelOnAllTeams($this, $teamNames);
    }
}
