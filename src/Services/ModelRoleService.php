<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelRoleService
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
    ) {}

    /**
     * Search model role assignments by role name.
     */
    public function searchAssignmentsByRoleNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $paginator = $this->modelHasRoleRepository->searchAssignmentsByRoleNameForModel($model, $roleNameSearchTerm, $pageNumber);

        return $paginator->through(function (ModelHasRole $modelHasRole) {
            $modelHasRole->offsetSet('assigned_at', $modelHasRole->created_at?->format('Y-m-d H:i:s T'));

            return $modelHasRole;
        });
    }

    /**
     * Search unassigned roles by role name for model.
     */
    public function searchUnassignedByRoleNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        return $this->modelHasRoleRepository->searchUnassignedByRoleNameForModel($model, $roleNameSearchTerm, $pageNumber);
    }
}
