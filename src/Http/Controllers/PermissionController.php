<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\PermissionPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\StorePermissionRequest;
use Gillyware\Gatekeeper\Http\Requests\Entities\Permission\UpdatePermissionRequest;
use Gillyware\Gatekeeper\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PermissionController extends Controller
{
    /**
     * Get a page of permissions.
     */
    public function index(PermissionPageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $importantAttribute = $request->validated('prioritized_attribute');
        $nameOrder = $request->validated('name_order');
        $isActiveOrder = $request->validated('is_active_order');

        if (! Permission::tableExists()) {
            return $this->errorResponse('The permissions table does not exist in the database.');
        }

        $query = Permission::query();

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
            $permission = Gatekeeper::createPermission($request->validated('name'));

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
            $permission = Gatekeeper::updatePermission($permission, $request->validated('name'));

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
            $permission = Gatekeeper::deactivatePermission($permission);

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
            $permission = Gatekeeper::reactivatePermission($permission);

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
            Gatekeeper::deletePermission($permission);

            return Response::json([], HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
