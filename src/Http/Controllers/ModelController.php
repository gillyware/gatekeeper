<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Constants\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Http\Requests\Model\AssignEntityToModelRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\LookupModelRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\RevokeEntityFromModelRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\SearchEntitiesForModelRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\SearchModelsRequest;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelPermissionService;
use Gillyware\Gatekeeper\Services\ModelRoleService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Services\ModelTeamService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ModelController extends Controller
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelService $modelService,
        private readonly ModelPermissionService $modelPermissionService,
        private readonly ModelRoleService $modelRoleService,
        private readonly ModelTeamService $modelTeamService,
    ) {}

    /**
     * Search for models based on a search term.
     */
    public function search(SearchModelsRequest $request): JsonResponse
    {
        try {
            return Response::json($this->modelService->searchModels(
                $request->validated('model_label'),
                (string) $request->validated('search_term')
            ));
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Search for entity assignments for a model.
     */
    public function searchEntityAssignmentsForModel(SearchEntitiesForModelRequest $request): JsonResponse
    {
        try {
            $modelLabel = $request->validated('model_label');
            $modelPk = $request->validated('model_pk');
            $pageNumber = $request->validated('page');
            $entity = $request->validated('entity');
            $entityNameSearchTerm = (string) $request->validated('search_term');

            $className = $this->modelMetadataService->getClassFromLabel($modelLabel);
            $model = $this->modelService->findModelInstance($className, $modelPk);

            $paginator = match ($entity) {
                GatekeeperEntity::PERMISSION => $this->modelPermissionService->searchAssignmentsByPermissionNameForModel($model, $entityNameSearchTerm, $pageNumber),
                GatekeeperEntity::ROLE => $this->modelRoleService->searchAssignmentsByRoleNameForModel($model, $entityNameSearchTerm, $pageNumber),
                GatekeeperEntity::TEAM => $this->modelTeamService->searchAssignmentsByTeamNameForModel($model, $entityNameSearchTerm, $pageNumber),
            };

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Search for unassigned entities for a model.
     */
    public function searchUnassignedEntitiesForModel(SearchEntitiesForModelRequest $request): JsonResponse
    {
        try {
            $modelLabel = $request->validated('model_label');
            $modelPk = $request->validated('model_pk');
            $pageNumber = $request->validated('page');
            $entity = $request->validated('entity');
            $entityNameSearchTerm = (string) $request->validated('search_term');

            $className = $this->modelMetadataService->getClassFromLabel($modelLabel);
            $model = $this->modelService->findModelInstance($className, $modelPk);

            $paginator = match ($entity) {
                GatekeeperEntity::PERMISSION => $this->modelPermissionService->searchUnassignedByPermissionNameForModel($model, $entityNameSearchTerm, $pageNumber),
                GatekeeperEntity::ROLE => $this->modelRoleService->searchUnassignedByRoleNameForModel($model, $entityNameSearchTerm, $pageNumber),
                GatekeeperEntity::TEAM => $this->modelTeamService->searchUnassignedByTeamNameForModel($model, $entityNameSearchTerm, $pageNumber),
            };

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Lookup a model by its primary key and return its access.
     */
    public function lookup(LookupModelRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('model_label');
            $pk = $request->validated('model_pk');

            $modelData = $this->modelMetadataService->getModelDataByLabel($label);
            $modelClass = $this->modelMetadataService->getClassFromModelData($modelData);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            return Response::json([
                'model_label' => $modelData['label'],
                'model_pk' => (string) $model->getKey(),
                'searchable' => $modelData['searchable'] ?? [],
                'displayable' => $modelData['displayable'] ?? [],

                'display' => $this->modelService->prepareModelForDisplay($modelData, $model),

                'is_permission' => $this->modelIsPermission($model),
                'is_role' => $this->modelIsRole($model),
                'is_team' => $this->modelIsTeam($model),

                'has_permissions' => $this->modelInteractsWithPermissions($model),
                'has_roles' => $this->modelInteractsWithRoles($model),
                'has_teams' => $this->modelInteractsWithTeams($model),

                'direct_permissions' => Gatekeeper::getDirectPermissionsForModel($model),
                'direct_roles' => Gatekeeper::getDirectRolesForModel($model),
                'direct_teams' => Gatekeeper::getDirectTeamsForModel($model),
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Assign an entity to a model.
     */
    public function assign(AssignEntityToModelRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('model_label');
            $pk = $request->validated('model_pk');
            $entity = $request->validated('entity');
            $entityName = $request->validated('entity_name');

            $modelClass = $this->modelMetadataService->getClassFromLabel($label);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            if (! $this->entityExists($entity, $entityName)) {
                return $this->errorResponse(ucfirst($entity).' does not exist');
            }

            match ($entity) {
                GatekeeperEntity::PERMISSION => Gatekeeper::assignPermissionToModel($model, $entityName),
                GatekeeperEntity::ROLE => Gatekeeper::assignRoleToModel($model, $entityName),
                GatekeeperEntity::TEAM => Gatekeeper::addModelToTeam($model, $entityName),
            };

            return Response::json([
                'message' => ucfirst($entity).' assigned successfully',
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Revoke an entity from a model.
     */
    public function revoke(RevokeEntityFromModelRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('model_label');
            $pk = $request->validated('model_pk');
            $entity = $request->validated('entity');
            $entityName = $request->validated('entity_name');

            $modelClass = $this->modelMetadataService->getClassFromLabel($label);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            if (! $this->entityExists($entity, $entityName)) {
                return $this->errorResponse(ucfirst($entity).' does not exist');
            }

            match ($entity) {
                GatekeeperEntity::PERMISSION => Gatekeeper::revokePermissionFromModel($model, $entityName),
                GatekeeperEntity::ROLE => Gatekeeper::revokeRoleFromModel($model, $entityName),
                GatekeeperEntity::TEAM => Gatekeeper::removeModelFromTeam($model, $entityName),
            };

            return Response::json([
                'message' => ucfirst($entity).' revoked successfully',
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Check if an entity exists based on the type and name.
     */
    private function entityExists(string $entity, string $entityName): bool
    {
        return match ($entity) {
            GatekeeperEntity::PERMISSION => Gatekeeper::permissionExists($entityName),
            GatekeeperEntity::ROLE => Gatekeeper::roleExists($entityName),
            GatekeeperEntity::TEAM => Gatekeeper::teamExists($entityName),
            default => false,
        };
    }
}
