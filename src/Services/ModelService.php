<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Models\AbstractModelHasGatekeeperEntity;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ModelService
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelHasPermissionRepository $modelHasPermissionRepository,
        private readonly ModelHasRoleRepository $modelHasRoleRepository,
        private readonly ModelHasTeamRepository $modelHasTeamRepository,
    ) {}

    /**
     * Resolve a model instance by its primary key.
     */
    public function findModelInstance(string $className, int|string $pk): Model
    {
        $model = $className::find($pk);

        if (! $model) {
            throw new GatekeeperException("Model with primary key '{$pk}' not found in class '{$className}'.");
        }

        return $model;
    }

    /**
     * Search for models based on a label and search term.
     */
    public function searchModels(string $modelLabel, string $searchTerm): Collection
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($modelLabel);
        $className = $this->modelMetadataService->getClassFromModelData($modelData ?? []);

        if (! $modelData || ! $className) {
            throw new GatekeeperException("Model with label '{$modelLabel}' not found or not manageable.");
        }

        $searchableColumns = collect($modelData['searchable'] ?? [])->pluck('column')->values()->all();
        $query = $className::query();

        foreach ($searchableColumns as $column) {
            $query->orWhere($column, 'like', "%{$searchTerm}%");
        }

        return $query->limit(10)->get()->map(fn (Model $model): array => [
            'model_label' => $modelData['label'],
            'model_pk' => (string) $model->getKey(),
            'searchable' => $modelData['searchable'] ?? [],
            'displayable' => $modelData['displayable'] ?? [],

            'display' => $this->prepareModelForDisplay($modelData, $model),

            'has_permissions' => $this->modelInteractsWithPermissions($model),
            'has_roles' => $this->modelInteractsWithRoles($model),
            'has_teams' => $this->modelInteractsWithTeams($model),
        ]);
    }

    /**
     * Search for models by permission name and model searchables.
     */
    public function searchByPermission(string $modelLabel, string $permissionNameSearchTerm, string $modelSearchTerm, int $page): LengthAwarePaginator
    {
        $paginator = $this->modelHasPermissionRepository->searchByPermission($modelLabel, $permissionNameSearchTerm, $modelSearchTerm, $page);

        return $this->processEntitySearchResults(GatekeeperEntity::PERMISSION, $modelLabel, $paginator);
    }

    /**
     * Search for models by role name and model searchables.
     */
    public function searchByRole(string $modelLabel, string $roleNameSearchTerm, string $modelSearchTerm, int $page): LengthAwarePaginator
    {
        $paginator = $this->modelHasRoleRepository->searchByRole($modelLabel, $roleNameSearchTerm, $modelSearchTerm, $page);

        return $this->processEntitySearchResults(GatekeeperEntity::ROLE, $modelLabel, $paginator);
    }

    /**
     * Search for models by team name and model searchables.
     */
    public function searchByTeam(string $modelLabel, string $teamNameSearchTerm, string $modelSearchTerm, int $page): LengthAwarePaginator
    {
        $paginator = $this->modelHasTeamRepository->searchByTeam($modelLabel, $teamNameSearchTerm, $modelSearchTerm, $page);

        return $this->processEntitySearchResults(GatekeeperEntity::TEAM, $modelLabel, $paginator);
    }

    /**
     * Prepare a model for display in the UI.
     */
    public function prepareModelForDisplay(array $modelData, Model $model): array
    {
        $result = [];

        foreach (($modelData['displayable'] ?? []) as $x) {
            $result[$x['column']] = $model->{$x['column']};
        }

        return $result;
    }

    private function processEntitySearchResults(string $entity, string $modelLabel, LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($modelLabel);
        $className = $this->modelMetadataService->getClassFromModelData($modelData);

        $modelInteractsWithPermissions = $this->modelInteractsWithPermissions($className);
        $modelInteractsWithRoles = $this->modelInteractsWithRoles($className);
        $modelInteractsWithTeams = $this->modelInteractsWithTeams($className);

        return $paginator->through(fn (AbstractModelHasGatekeeperEntity $modelHasEntity) => [
            'model_label' => $modelData['label'],
            'model_pk' => (string) $modelHasEntity->model->getKey(),
            'searchable' => $modelData['searchable'] ?? [],
            'displayable' => $modelData['displayable'] ?? [],

            'display' => $this->prepareModelForDisplay($modelData, $modelHasEntity->model),

            'has_permissions' => $modelInteractsWithPermissions,
            'has_roles' => $modelInteractsWithRoles,
            'has_teams' => $modelInteractsWithTeams,

            $entity => $modelHasEntity->{$entity},
            'assigned_at' => $modelHasEntity->created_at->format('Y-m-d H:i:s T'),
        ]);
    }
}
