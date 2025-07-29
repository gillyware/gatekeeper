<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Assign a permission to a model.
     *
     * @param  Permission  $permission
     */
    public function assignToModel(Model $model, $permission): ModelHasPermission
    {
        $modelHasPermission = ModelHasPermission::query()->updateOrCreate([
            'permission_id' => $permission->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => false,
        ]);

        $this->cacheService->invalidateCacheForModelPermissionLinks($model);

        return $modelHasPermission;
    }

    /**
     * Delete all non-denied permission assignments for a given model and permission.
     *
     * @param  Permission  $permission
     */
    public function unassignFromModel(Model $model, $permission): bool
    {
        ModelHasPermission::forModel($model)
            ->where('permission_id', $permission->id)
            ->where('denied', false)
            ->delete();

        $this->cacheService->invalidateCacheForModelPermissionLinks($model);

        return true;
    }

    /**
     * Deny a permission from a model.
     *
     * @param  Permission  $permission
     */
    public function denyFromModel(Model $model, $permission): ModelHasPermission
    {
        $modelHasPermission = ModelHasPermission::query()->updateOrCreate([
            'permission_id' => $permission->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => true,
        ]);

        $this->cacheService->invalidateCacheForModelPermissionLinks($model);

        return $modelHasPermission;
    }

    /**
     * Delete all denied permission assignments for a given model and permission.
     *
     * @param  Permission  $permission
     */
    public function undenyFromModel(Model $model, $permission): bool
    {
        ModelHasPermission::forModel($model)
            ->where('permission_id', $permission->id)
            ->where('denied', true)
            ->delete();

        $this->cacheService->invalidateCacheForModelPermissionLinks($model);

        return true;
    }

    /**
     * Delete all permission assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasPermission::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelPermissionLinks($model);

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
                    $this->cacheService->invalidateCacheForModelPermissionLinks($modelHasPermission->model);
                }
            });

        return true;
    }

    /**
     * Search model permission assignments by permission name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasPermission::query()
            ->select((new ModelHasPermission)->qualifyColumn('*'))
            ->join((new Permission)->getTable(), (new Permission)->qualifyColumn('id'), '=', (new ModelHasPermission)->qualifyColumn('permission_id'))
            ->forModel($model)
            ->where('denied', false)
            ->whereIn('permission_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Permission)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('permission:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned permissions by permission name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return Permission::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model) {
                $subquery->select('permission_id')
                    ->from((new ModelHasPermission)->getTable())
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull((new ModelHasPermission)->qualifyColumn('deleted_at'));
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search denied permissions by permission name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasPermission::query()
            ->select((new ModelHasPermission)->qualifyColumn('*'))
            ->join((new Permission)->getTable(), (new Permission)->qualifyColumn('id'), '=', (new ModelHasPermission)->qualifyColumn('permission_id'))
            ->forModel($model)
            ->where('denied', true)
            ->whereIn('permission_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Permission)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('permission:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }
}
