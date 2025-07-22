<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityServiceInterface;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class ModelHasRoleService implements ModelHasEntityServiceInterface
{
    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
    ) {}

    /**
     * Search model role assignments by role name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $paginator = $this->modelHasRoleRepository->searchAssignmentsByEntityNameForModel($model, $packet);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return $paginator->through(function (ModelHasRole $modelHasRole) use ($displayTimezone) {
            $modelHasRole->offsetSet('assigned_at', $modelHasRole->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'));

            return $modelHasRole;
        });
    }

    /**
     * Search unassigned roles by role name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return $this->modelHasRoleRepository->searchUnassignedByEntityNameForModel($model, $packet)
            ->through(fn (Role $role) => $role->toPacket());
    }
}
