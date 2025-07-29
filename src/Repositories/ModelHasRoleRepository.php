<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @implements ModelHasEntityRepositoryInterface<Role, ModelHasRole>
 */
class ModelHasRoleRepository implements ModelHasEntityRepositoryInterface
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a role is assigned to any model.
     *
     * @param  Role  $role
     */
    public function existsForEntity($role): bool
    {
        return ModelHasRole::query()->where('role_id', $role->id)->exists();
    }

    /**
     * Assign a role to a model.
     *
     * @param  Role  $role
     */
    public function assignToModel(Model $model, $role): ModelHasRole
    {
        $modelHasRole = ModelHasRole::query()->updateOrCreate([
            'role_id' => $role->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => false,
        ]);

        $this->cacheService->invalidateCacheForModelRoleLinks($model);

        return $modelHasRole;
    }

    /**
     * Delete all un-denied role assignments for a given model and role.
     *
     * @param  Role  $role
     */
    public function unassignFromModel(Model $model, $role): bool
    {
        ModelHasRole::forModel($model)
            ->where('role_id', $role->id)
            ->where('denied', false)
            ->delete();

        $this->cacheService->invalidateCacheForModelRoleLinks($model);

        return true;
    }

    /**
     * Deny a role from a model.
     *
     * @param  Role  $role
     */
    public function denyFromModel(Model $model, $role): ModelHasRole
    {
        $modelHasRole = ModelHasRole::query()->updateOrCreate([
            'role_id' => $role->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], [
            'denied' => true,
        ]);

        $this->cacheService->invalidateCacheForModelRoleLinks($model);

        return $modelHasRole;
    }

    /**
     * Delete all denied role assignments for a given model and role.
     *
     * @param  Role  $role
     */
    public function undenyFromModel(Model $model, $role): bool
    {
        ModelHasRole::forModel($model)
            ->where('role_id', $role->id)
            ->where('denied', true)
            ->delete();

        $this->cacheService->invalidateCacheForModelRoleLinks($model);

        return true;
    }

    /**
     * Delete all role assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasRole::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelRoleLinks($model);

        return true;
    }

    /**
     * Delete all assignments for a given role.
     *
     * @param  Role  $role
     */
    public function deleteForEntity($role): bool
    {
        ModelHasRole::query()->where('role_id', $role->id)
            ->with('model')
            ->get()
            ->each(function (ModelHasRole $modelHasRole) {
                $modelHasRole->delete();

                if ($modelHasRole->model) {
                    $this->cacheService->invalidateCacheForModelRoleLinks($modelHasRole->model);
                }
            });

        return true;
    }

    /**
     * Search model role assignments by role name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasRole::query()
            ->select((new ModelHasRole)->qualifyColumn('*'))
            ->join((new Role)->getTable(), (new Role)->qualifyColumn('id'), '=', (new ModelHasRole)->qualifyColumn('role_id'))
            ->forModel($model)
            ->where('denied', false)
            ->whereIn('role_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Role)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('role:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search unassigned roles by role name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return Role::query()
            ->whereLike('name', "%{$packet->searchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model) {
                $subquery->select('role_id')
                    ->from((new ModelHasRole)->getTable())
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull((new ModelHasRole)->qualifyColumn('deleted_at'));
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10, ['*'], 'page', $packet->page);
    }

    /**
     * Search denied roles by role name for model.
     */
    public function searchDeniedByEntityNameForModel(Model $model, ModelEntitiesPagePacket $packet): LengthAwarePaginator
    {
        return ModelHasRole::query()
            ->select((new ModelHasRole)->qualifyColumn('*'))
            ->join((new Role)->getTable(), (new Role)->qualifyColumn('id'), '=', (new ModelHasRole)->qualifyColumn('role_id'))
            ->forModel($model)
            ->where('denied', true)
            ->whereIn('role_id', function ($sub) use ($packet) {
                $sub->select('id')
                    ->from((new Role)->getTable())
                    ->whereLike('name', "%{$packet->searchTerm}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->with('role:id,name,grant_by_default,is_active')
            ->paginate(10, ['*'], 'page', $packet->page);
    }
}
