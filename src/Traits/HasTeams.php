<?php

namespace Gillyware\Gatekeeper\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use UnitEnum;

trait HasTeams
{
    use InteractsWithTeams;

    /**
     * Assign a team to the model.
     */
    public function addToTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->addToTeam($team);
    }

    /**
     * Assign multiple teams to the model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function addToAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->addToAllTeams($teams);
    }

    /**
     * Unassign a team from the model.
     */
    public function removeFromTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->removeFromTeam($team);
    }

    /**
     * Unassign multiple teams from the model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function removeFromAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->removeFromAllTeams($teams);
    }

    /**
     * Deny a team from the model.
     */
    public function denyTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->denyTeam($team);
    }

    /**
     * Deny multiple teams from the model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function denyAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->denyAllTeams($teams);
    }

    /**
     * Undeny a team from the model.
     */
    public function undenyTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->undenyTeam($team);
    }

    /**
     * Undeny multiple teams from the model.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function undenyAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->undenyAllTeams($teams);
    }

    /**
     * Check if the model has a given team.
     */
    public function onTeam(Team|TeamPacket|string|UnitEnum $team): bool
    {
        return Gatekeeper::for($this)->onTeam($team);
    }

    /**
     * Check if the model is on any of the given teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function onAnyTeam(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->onAnyTeam($teams);
    }

    /**
     * Check if the model is on all of the given teams.
     *
     * @param  array<Team|TeamPacket|string|UnitEnum>|Arrayable<Team|TeamPacket|string|UnitEnum>  $teams
     */
    public function onAllTeams(array|Arrayable $teams): bool
    {
        return Gatekeeper::for($this)->onAllTeams($teams);
    }

    /**
     * Get all teams assigned directly to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getDirectTeams(): Collection
    {
        return Gatekeeper::for($this)->getDirectTeams();
    }

    /**
     * Get all teams assigned directly or indirectly to a model.
     *
     * @return Collection<string, TeamPacket>
     */
    public function getEffectiveTeams(): Collection
    {
        return Gatekeeper::for($this)->getEffectiveTeams();
    }

    /**
     * Get all effective teams for the given model with the team source(s).
     */
    public function getVerboseTeams(): Collection
    {
        return Gatekeeper::for($this)->getVerboseTeams();
    }
}
