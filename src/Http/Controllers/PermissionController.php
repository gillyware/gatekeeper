<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\PermissionPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\StorePermissionRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\UpdatePermissionRequest;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PermissionController extends AbstractBaseController
{
    public function __construct(private readonly PermissionService $permissionService) {}

    /**
     * Get a page of permissions.
     */
    public function index(PermissionPageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $searchTerm = (string) $request->validated('search_term');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! $this->permissionService->tableExists()) {
            return $this->errorResponse('The permissions table does not exist in the database.');
        }

        return Response::json(
            $this->permissionService->getPage($pageNumber, $searchTerm, $importantAttribute, $nameOrder, $isActiveOrder)
        );
    }

    /**
     * Get a permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        return Response::json($permission);
    }

    /**
     * Create a new permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        try {
            $permissionName = $request->validated('name');
            $permission = $this->permissionService->create($permissionName);

            return Response::json($permission, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        try {
            $newPermissionName = $request->validated('name');
            $permission = $this->permissionService->update($permission, $newPermissionName);

            return Response::json($permission);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a permission.
     */
    public function deactivate(Permission $permission): JsonResponse
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
    public function reactivate(Permission $permission): JsonResponse
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
    public function delete(Permission $permission): JsonResponse
    {
        try {
            $this->permissionService->delete($permission);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
