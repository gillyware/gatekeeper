<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\StoreRolePacket;
use Gillyware\Gatekeeper\Packets\Entities\Role\UpdateRolePacket;
use Gillyware\Gatekeeper\Services\RoleService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class RoleController extends AbstractBaseController
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Get a page of roles.
     */
    public function index(EntityPagePacket $packet): HttpFoundationResponse
    {
        if (! $this->roleService->tableExists()) {
            return $this->errorResponse('The roles table does not exist in the database.');
        }

        return Response::json($this->roleService->getPage($packet));
    }

    /**
     * Get a role.
     */
    public function show(Role $role): HttpFoundationResponse
    {
        return Response::json($role->toPacket());
    }

    /**
     * Create a new role.
     */
    public function store(StoreRolePacket $packet): HttpFoundationResponse
    {
        try {
            $role = $this->roleService->create($packet->name);

            return Response::json($role, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing role.
     */
    public function update(UpdateRolePacket $packet, Role $role): HttpFoundationResponse
    {
        try {
            $role = $this->roleService->update($role, $packet->name);

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
