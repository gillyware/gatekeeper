<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ModelHasRoleRepository
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ModelMetadataService $modelMetadataService,
    ) {}

    /**
     * Check if a role is assigned to any model.
     */
    public function existsForRole(Role $role): bool
    {
        return ModelHasRole::query()->where('role_id', $role->id)->exists();
    }

    /**
     * Create a new ModelHasRole instance.
     */
    public function create(Model $model, Role $role): ModelHasRole
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
     * Get all ModelHasRole instances for a given model and role.
     */
    public function getForModelAndRole(Model $model, Role $role): Collection
    {
        return ModelHasRole::forModel($model)->where('role_id', $role->id)->get();
    }

    /**
     * Get the most recent ModelHasRole instance for a given model and role, including trashed instances.
     */
    public function getRecentForModelAndRoleIncludingTrashed(Model $model, Role $role): ?ModelHasRole
    {
        return ModelHasRole::forModel($model)
            ->where('role_id', $role->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    /**
     * Delete all ModelHasRole instances for a given model and role.
     */
    public function deleteForModelAndRole(Model $model, Role $role): bool
    {
        $this->getForModelAndRole($model, $role)->each(function (ModelHasRole $modelHasRole) {
            $modelHasRole->delete();
        });

        $this->cacheService->invalidateCacheForModelRoleNames($model);

        return true;
    }

    /**
     * Search for models by role name and model searchables.
     */
    public function searchByRole(string $modelLabel, string $roleNameSearchTerm, string $modelSearchTerm, int $page): LengthAwarePaginator
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($modelLabel);
        $className = $this->modelMetadataService->getClassFromModelData($modelData);

        $searchableColumns = array_keys($modelData['searchable'] ?? []);
        $displayableColumns = array_keys($modelData['displayable'] ?? []);
        $primaryKey = (new $className)->getKeyName();

        $roleIds = Role::query()
            ->where('name', 'like', "%{$roleNameSearchTerm}%")
            ->pluck('id');

        $query = ModelHasRole::query()
            ->where('model_type', $className)
            ->whereIn('role_id', $roleIds)
            ->whereHasMorph('model', $className, function ($query) use ($searchableColumns, $modelSearchTerm) {
                $query->where(function ($query) use ($searchableColumns, $modelSearchTerm) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($column, 'like', "%{$modelSearchTerm}%");
                    }
                });
            })
            ->with([
                'role:id,name,is_active',
                'model' => function ($query) use ($displayableColumns, $primaryKey) {
                    $query->select(array_merge([$primaryKey], $displayableColumns));
                },
            ]);

        return $query->paginate(10, ['*'], 'page', $page);
    }

    /**
     * Search model role assignments by role name.
     */
    public function searchAssignmentsByRoleNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $rolesTable = Config::get('gatekeeper.tables.roles');
        $modelRolesTable = Config::get('gatekeeper.tables.model_has_roles');

        $query = ModelHasRole::query()
            ->select("$modelRolesTable.*")
            ->join($rolesTable, "$rolesTable.id", '=', "$modelRolesTable.role_id")
            ->forModel($model)
            ->whereIn('role_id', function ($sub) use ($rolesTable, $roleNameSearchTerm) {
                $sub->select('id')
                    ->from($rolesTable)
                    ->where('name', 'like', "%{$roleNameSearchTerm}%");
            })
            ->orderBy("$rolesTable.is_active")
            ->orderBy("$rolesTable.name")
            ->with('role:id,name,is_active');

        return $query->paginate(10, ['*'], 'page', $pageNumber);
    }

    /**
     * Search unassigned roles by role name for model.
     */
    public function searchUnassignedByRoleNameForModel(Model $model, string $roleNameSearchTerm, int $pageNumber): LengthAwarePaginator
    {
        $modelRolesTable = Config::get('gatekeeper.tables.model_has_roles');

        $query = Role::query()
            ->where('name', 'like', "%{$roleNameSearchTerm}%")
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
