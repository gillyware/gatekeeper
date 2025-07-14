<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class ModelHasPermissionRepository
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a permission is assigned to any model.
     */
    public function existsForPermission(Permission $permission): bool
    {
        return ModelHasPermission::query()->where('permission_id', $permission->id)->exists();
    }

    /**
     * Create a new ModelHasPermission instance.
     */
    public function create(Model $model, Permission $permission): ModelHasPermission
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
     * Delete all permission assignments for a given model and permission.
     */
    public function deleteForModelAndPermission(Model $model, Permission $permission): bool
    {
        ModelHasPermission::forModel($model)->where('permission_id', $permission->id)->delete();

        $this->cacheService->invalidateCacheForModelPermissionNames($model);

        return true;
    }

    /**
     * Search model permission assignments by permission name.
     */
    public function searchAssignmentsByPermissionNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $permissionsTable = Config::get('gatekeeper.tables.permissions', GatekeeperConfigDefault::TABLES_PERMISSIONS);
        $modelPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS);

        $query = ModelHasPermission::query()
            ->select("$modelPermissionsTable.*")
            ->join($permissionsTable, "$permissionsTable.id", '=', "$modelPermissionsTable.permission_id")
            ->forModel($model)
            ->whereIn('permission_id', function ($sub) use ($permissionsTable, $permissionNameSearchTerm) {
                $sub->select('id')
                    ->from($permissionsTable)
                    ->whereLike('name', "%{$permissionNameSearchTerm}%");
            })
            ->orderBy("$permissionsTable.is_active")
            ->orderBy("$permissionsTable.name")
            ->with('permission:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByPermissionNameForModel(Model $model, string $permissionNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $modelPermissionsTable = Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS);

        $query = Permission::query()
            ->whereLike('name', "%{$permissionNameSearchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelPermissionsTable) {
                $subquery->select('permission_id')
                    ->from($modelPermissionsTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelPermissionsTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }
}
