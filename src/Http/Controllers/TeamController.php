<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\StoreTeamRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\TeamPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\UpdateTeamRequest;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class TeamController extends AbstractBaseController
{
    public function __construct(private readonly TeamService $teamService) {}

    /**
     * Get a page of teams.
     */
    public function index(TeamPageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $searchTerm = (string) $request->validated('search_term');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! $this->teamService->tableExists()) {
            return $this->errorResponse('The teams table does not exist in the database.');
        }

        return Response::json(
            $this->teamService->getPage($pageNumber, $searchTerm, $importantAttribute, $nameOrder, $isActiveOrder)
        );
    }

    /**
     * Get a team.
     */
    public function show(Team $team): JsonResponse
    {
        return Response::json($team);
    }

    /**
     * Create a new team.
     */
    public function store(StoreTeamRequest $request): JsonResponse
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
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
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
    public function deactivate(Team $team): JsonResponse
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
    public function reactivate(Team $team): JsonResponse
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
    public function delete(Team $team): JsonResponse
    {
        try {
            $this->teamService->delete($team);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
