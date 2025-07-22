<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\StorePermissionPacket;
use Gillyware\Gatekeeper\Packets\Entities\Permission\UpdatePermissionPacket;
use Gillyware\Gatekeeper\Services\PermissionService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PermissionController extends AbstractBaseController
{
    public function __construct(private readonly PermissionService $permissionService) {}

    /**
     * Get a page of permissions.
     */
    public function index(EntityPagePacket $packet): HttpFoundationResponse
    {
        if (! $this->permissionService->tableExists()) {
            return $this->errorResponse('The permissions table does not exist in the database.');
        }

        return Response::json($this->permissionService->getPage($packet));
    }

    /**
     * Get a permission.
     */
    public function show(Permission $permission): HttpFoundationResponse
    {
        return Response::json($permission->toPacket());
    }

    /**
     * Create a new permission.
     */
    public function store(StorePermissionPacket $packet): HttpFoundationResponse
    {
        try {
            $permission = $this->permissionService->create($packet->name);

            return Response::json($permission, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing permission.
     */
    public function update(UpdatePermissionPacket $packet, Permission $permission): HttpFoundationResponse
    {
        try {
            $permission = $this->permissionService->update($permission, $packet->name);

            return Response::json($permission);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a permission.
     */
    public function deactivate(Permission $permission): HttpFoundationResponse
    {
        try {
            $permission = $this->permissionService->deactivate($permission);

            return Response::json($permission);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reactivate a permission.
     */
    public function reactivate(Permission $permission): HttpFoundationResponse
    {
        try {
            $permission = $this->permissionService->reactivate($permission);

            return Response::json($permission);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a permission.
     */
    public function delete(Permission $permission): HttpFoundationResponse
    {
        try {
            $this->permissionService->delete($permission);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
