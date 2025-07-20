<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\RolePageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\StoreRoleRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Role\UpdateRoleRequest;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\RoleService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class RoleController extends AbstractBaseController
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Get a page of roles.
     */
    public function index(RolePageRequest $request): HttpFoundationResponse
    {
        $pageNumber = $request->validated('page');
        $searchTerm = (string) $request->validated('search_term');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! $this->roleService->tableExists()) {
            return $this->errorResponse('The roles table does not exist in the database.');
        }

        return Response::json(
            $this->roleService->getPage($pageNumber, $searchTerm, $importantAttribute, $nameOrder, $isActiveOrder)
        );
    }

    /**
     * Get a role.
     */
    public function show(Role $role): HttpFoundationResponse
    {
        return Response::json($role);
    }

    /**
     * Create a new role.
     */
    public function store(StoreRoleRequest $request): HttpFoundationResponse
    {
        try {
            $roleName = $request->validated('name');
            $role = $this->roleService->create($roleName);

            return Response::json($role, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing role.
     */
    public function update(UpdateRoleRequest $request, Role $role): HttpFoundationResponse
    {
        try {
            $newRoleName = $request->validated('name');
            $role = $this->roleService->update($role, $newRoleName);

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a role.
     */
    public function deactivate(Role $role): HttpFoundationResponse
    {
        try {
            $role = $this->roleService->deactivate($role);

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reactivate a role.
     */
    public function reactivate(Role $role): HttpFoundationResponse
    {
        try {
            $role = $this->roleService->reactivate($role);

            return Response::json($role);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): HttpFoundationResponse
    {
        try {
            $this->roleService->delete($role);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
