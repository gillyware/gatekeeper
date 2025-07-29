<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\StoreTeamPacket;
use Gillyware\Gatekeeper\Packets\Entities\Team\UpdateTeamPacket;
use Gillyware\Gatekeeper\Services\TeamService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class TeamController extends AbstractBaseController
{
    public function __construct(private readonly TeamService $teamService) {}

    /**
     * Get a page of teams.
     */
    public function index(EntityPagePacket $packet): HttpFoundationResponse
    {
        if (! $this->teamService->tableExists()) {
            return $this->errorResponse('The teams table does not exist in the database.');
        }

        return Response::json($this->teamService->getPage($packet));
    }

    /**
     * Get a team.
     */
    public function show(Team $team): HttpFoundationResponse
    {
        return Response::json($team->toPacket());
    }

    /**
     * Create a new team.
     */
    public function store(StoreTeamPacket $packet): HttpFoundationResponse
    {
        try {
            $team = $this->teamService->create($packet->name);

            return Response::json($team, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing team.
     */
    public function update(Team $team, UpdateTeamPacket $packet): HttpFoundationResponse
    {
        try {
            $team = $this->teamService->update($team, $packet);

            return Response::json($team);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a team.
     */
    public function delete(Team $team): HttpFoundationResponse
    {
        try {
            $this->teamService->delete($team);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
