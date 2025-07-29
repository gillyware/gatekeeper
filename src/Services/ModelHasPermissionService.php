<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityServiceInterface;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
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
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $paginator = $this->modelHasPermissionRepository->searchAssignmentsByEntityNameForModel($model, $packet);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasPermission $modelHasPermission) use ($displayTimezone) {
            $modelHasPermission->offsetSet('assigned_at', $modelHasPermission->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

            return $modelHasPermission;
        });
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return $this->modelHasPermissionRepository->searchUnassignedByEntityNameForModel($model, $packet)
            ->through(fn (Permission $permission) => $permission->toPacket());
    }

    /**
     * Search denied permissions by permission name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $paginator = $this->modelHasPermissionRepository->searchDeniedByEntityNameForModel($model, $packet);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasPermission $modelHasPermission) use ($displayTimezone) {
            $modelHasPermission->offsetSet('denied_at', $modelHasPermission->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

            return $modelHasPermission;
        });
    }
}
