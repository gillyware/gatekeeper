<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelPermissionService
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    /**
     * Search model permission assignments by permission name.
     */
    public function searchAssignmentsByPermissionNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $paginator = $this->modelHasPermissionRepository->searchAssignmentsByPermissionNameForModel($model, $permissionNameSearchTerm, $pageNumber);

        return $paginator->through(function (ModelHasPermission $modelHasPermission) {
            $modelHasPermission->offsetSet('assigned_at', $modelHasPermission->created_at->format('Y-m-d H:i:s T'));

            return $modelHasPermission;
        });
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByPermissionNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        return $this->modelHasPermissionRepository->searchUnassignedByPermissionNameForModel($model, $permissionNameSearchTerm, $pageNumber);
    }
}
