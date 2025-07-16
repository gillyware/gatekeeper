<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
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
    public function getModels(string $modelLabel, string $searchTerm): Collection
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($modelLabel);
        $className = $this->modelMetadataService->getClassFromModelData($modelData ?? []);

        if (! $modelData || ! $className) {
            throw new GatekeeperException("Model with label '{$modelLabel}' not found or not manageable.");
        }

        $searchableColumns = collect($modelData['searchable'] ?? [])->pluck('column')->values()->all();
        $query = $className::query();

        foreach ($searchableColumns as $column) {
            $query->orWhereLike($column, "%{$searchTerm}%");
        }

        return $query->limit(10)->get()->map(fn (Model $model): array => [
            'model_label' => $modelData['label'],
            'model_pk' => (string) $model->getKey(),
            'searchable' => $modelData['searchable'] ?? [],
            'displayable' => $modelData['displayable'] ?? [],

            'display' => $this->prepareModelForDisplay($modelData, $model),

            'is_permission' => $this->modelIsPermission($model),
            'is_role' => $this->modelIsRole($model),
            'is_team' => $this->modelIsTeam($model),

            'has_permissions' => $this->modelInteractsWithPermissions($model),
            'has_roles' => $this->modelInteractsWithRoles($model),
            'has_teams' => $this->modelInteractsWithTeams($model),
        ]);
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
}
