<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityServiceInterface;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class ModelHasPermissionService implements ModelHasEntityServiceInterface
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
    ) {}

    /**
     * Search model permission assignments by permission name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $paginator = $this->modelHasPermissionRepository->searchAssignmentsByEntityNameForModel($model, $permissionNameSearchTerm, $pageNumber);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasPermission $modelHasPermission) use ($displayTimezone) {
            $modelHasPermission->offsetSet('assigned_at', $modelHasPermission->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

            return $modelHasPermission;
        });
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        return $this->modelHasPermissionRepository->searchUnassignedByEntityNameForModel($model, $permissionNameSearchTerm, $pageNumber);
    }
}
