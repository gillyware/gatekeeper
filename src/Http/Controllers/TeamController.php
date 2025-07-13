<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\StoreTeamRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\TeamPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Team\UpdateTeamRequest;
use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class TeamController extends Controller
{
    /**
     * Get a page of teams.
     */
    public function index(TeamPageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! Team::tableExists()) {
            return $this->errorResponse('The teams table does not exist in the database.');
        }

        $query = Team::query();

        if ($importantAttribute === 'is_active') {
            $query = $query
                ->orderBy('is_active', $isActiveOrder)
                ->orderBy('name', $nameOrder);
        } else {
            $query = $query
                ->orderBy('name', $nameOrder)
                ->orderBy('is_active', $isActiveOrder);
        }

        $paginator = $query->paginate(10, ['*'], 'page', $pageNumber);

        return Response::json($paginator);
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
            $team = Gatekeeper::createTeam($request->validated('name'));

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
            $team = Gatekeeper::updateTeam($team, $request->validated('name'));

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
            $team = Gatekeeper::deactivateTeam($team);

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
            $team = Gatekeeper::reactivateTeam($team);

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
            Gatekeeper::deleteTeam($team);

            return Response::json([], HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
