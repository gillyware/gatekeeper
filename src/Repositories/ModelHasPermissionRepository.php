<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

/**
 * @implements ModelHasEntityRepositoryInterface<Permission, ModelHasPermission>
 */
class ModelHasPermissionRepository implements ModelHasEntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a permission is assigned to any model.
     *
     * @param  Permission  $permission
     */
    public function existsForEntity($permission): bool
    {
        return ModelHasPermission::query()->where('permission_id', $permission->id)->exists();
    }

    /**
     * Create a new model permission assigment.
     *
     * @param  Permission  $permission
     */
    public function create(Model $model, $permission): ModelHasPermission
    {
        $modelHasPermission = ModelHasPermission::create([
            'permission_id' => $permission->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);

        $this->cacheService->invalidateCacheForModelPermissionNames($model);

        return $modelHasPermission;
    }

    /**
     * Delete all permission assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasPermission::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelPermissionNames($model);

        return true;
    }

    /**
     * Delete all assignments for a given permission.
     *
     * @param  Permission  $permission
     */
    public function deleteForEntity($permission): bool
    {
        ModelHasPermission::query()->where('permission_id', $permission->id)
            ->with('model')
            ->get()
            ->each(function (ModelHasPermission $modelHasPermission) {
                $modelHasPermission->delete();

                if ($modelHasPermission->model) {
                    $this->cacheService->invalidateCacheForModelPermissionNames($modelHasPermission->model);
                }
            });

        return true;
    }

    /**
     * Delete all permission assignments for a given model and permission.
     *
     * @param  Permission  $permission
     */
    public function deleteForModelAndEntity(Model $model, $permission): bool
    {
        ModelHasPermission::forModel($model)->where('permission_id', $permission->id)->delete();

        $this->cacheService->invalidateCacheForModelPermissionNames($model);

        return true;
    }

    /**
     * Search model permission assignments by permission name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $permissionsTable = Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
        $modelPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS);

        $query = ModelHasPermission::query()
            ->select("$modelPermissionsTable.*")
            ->join($permissionsTable, "$permissionsTable.id", '=', "$modelPermissionsTable.permission_id")
            ->forModel($model)
            ->whereIn('permission_id', function ($sub) use ($permissionsTable, $packet) {
                $sub->select('id')
                    ->from($permissionsTable)
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc("$permissionsTable.is_active")
            ->orderBy("$permissionsTable.name")
            ->with('permission:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        $modelPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS);

        $query = Permission::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelPermissionsTable) {
                $subquery->select('permission_id')
                    ->from($modelPermissionsTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelPermissionsTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $packet->page);
    }
}
