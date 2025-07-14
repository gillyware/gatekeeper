<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\RolePageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\StoreRoleRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\UpdateRoleRequest;
use Gillyware\Gatekeeper\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class RoleController extends Controller
{
    /**
     * Get a page of roles.
     */
    public function index(RolePageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! Role::tableExists()) {
            return $this->errorResponse('The roles table does not exist in the database.');
        }

        $query = Role::query();

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
     * Get a role.
     */
    public function show(Role $role): JsonResponse
    {
        return Response::json($role);
    }

    /**
     * Create a new role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = Gatekeeper::createRole($request->validated('name'));

            return Response::json($role, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing role.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        try {
            $role = Gatekeeper::updateRole($role, $request->validated('name'));

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a role.
     */
    public function deactivate(Role $role): JsonResponse
    {
        try {
            $role = Gatekeeper::deactivateRole($role);

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reactivate a role.
     */
    public function reactivate(Role $role): JsonResponse
    {
        try {
            $role = Gatekeeper::reactivateRole($role);

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): JsonResponse
    {
        try {
            Gatekeeper::deleteRole($role);

            return Response::json([], HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
