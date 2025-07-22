<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\StoreTeamRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\UpdateTeamRequest;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Services\TeamService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class TeamController extends AbstractBaseController
{
    public function __construct(private readonly TeamService $teamService) {}

    /**
     * Get a page of teams.
     */
    public function index(EntityPagePacket $entityPagePacket): HttpFoundationResponse
    {
        if (! $this->teamService->tableExists()) {
            return $this->errorResponse('The teams table does not exist in the database.');
        }

        return Response::json($this->teamService->getPage($entityPagePacket));
    }

    /**
     * Get a team.
     */
    public function show(Team $team): HttpFoundationResponse
    {
        return Response::json($team);
    }

    /**
     * Create a new team.
     */
    public function store(StoreTeamRequest $request): HttpFoundationResponse
    {
        try {
            $teamName = $request->validated('name');
            $team = $this->teamService->create($teamName);

            return Response::json($team, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing team.
     */
    public function update(UpdateTeamRequest $request, Team $team): HttpFoundationResponse
    {
        try {
            $newTeamName = $request->validated('name');
            $team = $this->teamService->update($team, $newTeamName);

            return Response::json($team);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a team.
     */
    public function deactivate(Team $team): HttpFoundationResponse
    {
        try {
            $team = $this->teamService->deactivate($team);

            return Response::json($team);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reactivate a team.
     */
    public function reactivate(Team $team): HttpFoundationResponse
    {
        try {
            $team = $this->teamService->reactivate($team);

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
