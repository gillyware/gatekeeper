<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\ModelHasEntityRepositoryInterface;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

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
     * Create a new model role assigment.
     *
     * @param  Role  $role
     */
    public function create(Model $model, $role): ModelHasRole
    {
        $modelHasRole = ModelHasRole::create([
            'role_id' => $role->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);

        $this->cacheService->invalidateCacheForModelRoleNames($model);

        return $modelHasRole;
    }

    /**
     * Delete all role assignments for a given model.
     */
    public function deleteForModel(Model $model): bool
    {
        ModelHasRole::forModel($model)->delete();

        $this->cacheService->invalidateCacheForModelRoleNames($model);

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
                    $this->cacheService->invalidateCacheForModelRoleNames($modelHasRole->model);
                }
            });

        return true;
    }

    /**
     * Delete all role assignments for a given model and role.
     *
     * @param  Role  $role
     */
    public function deleteForModelAndEntity(Model $model, $role): bool
    {
        ModelHasRole::forModel($model)->where('role_id', $role->id)->delete();

        $this->cacheService->invalidateCacheForModelRoleNames($model);

        return true;
    }

    /**
     * Search model role assignments by role name.
     */
    public function searchAssignmentsByEntityNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $rolesTable = Config::get('gatekeeper.tables.roles', GatekeeperConfigDefault::TABLES_ROLES);
        $modelRolesTable = Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES);

        $query = ModelHasRole::query()
            ->select("$modelRolesTable.*")
            ->join($rolesTable, "$rolesTable.id", '=', "$modelRolesTable.role_id")
            ->forModel($model)
            ->whereIn('role_id', function ($sub) use ($rolesTable, $roleNameSearchTerm) {
                $sub->select('id')
                    ->from($rolesTable)
                    ->whereLike('name', "%{$roleNameSearchTerm}%");
            })
            ->orderByDesc("$rolesTable.is_active")
            ->orderBy("$rolesTable.name")
            ->with('role:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }

    /**
     * Search unassigned roles by role name for model.
     */
    public function searchUnassignedByEntityNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $modelRolesTable = Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES);

        $query = Role::query()
            ->whereLike('name', "%{$roleNameSearchTerm}%")
            ->whereNotIn('id', function ($subquery) use ($model, $modelRolesTable) {
                $subquery->select('role_id')
                    ->from($modelRolesTable)
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->whereNull("$modelRolesTable.deleted_at");
            })
            ->orderByDesc('is_active')
            ->orderBy('name');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }
}
