<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Factories\EntityServiceFactory;
use Gillyware\Gatekeeper\Factories\ModelHasEntityServiceFactory;
use Gillyware\Gatekeeper\Http\Requests\Model\ModelEntitiesPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\ModelEntityRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\ModelPageRequest;
use Gillyware\Gatekeeper\Http\Requests\Model\ShowModelRequest;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ModelController extends AbstractBaseController
{
    use EnforcesForGatekeeper;

    public function __construct(
        private readonly ModelMetadataService $modelMetadataService,
        private readonly ModelService $modelService,
    ) {}

    /**
     * Get a page of labels matching the label and search term.
     */
    public function index(ModelPageRequest $request): JsonResponse
    {
        try {
            return Response::json($this->modelService->getModels(
                $request->validated('model_label'),
                (string) $request->validated('search_term')
            ));
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a model and its access.
     */
    public function show(ShowModelRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('modelLabel');
            $pk = $request->validated('modelPk');

            $modelData = $this->modelMetadataService->getModelDataByLabel($label);
            $modelClass = $this->modelMetadataService->getClassFromModelData($modelData);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            $verbosePermissions = Gatekeeper::for($model)->getVerbosePermissions();
            $verboseRoles = Gatekeeper::for($model)->getVerboseRoles();
            $directPermissionsCount = Gatekeeper::for($model)->getDirectPermissions()->count();
            $directRolesCount = Gatekeeper::for($model)->getDirectRoles()->count();
            $directTeamsCount = Gatekeeper::for($model)->getTeams()->count();

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

                'access_sources' => [
                    'permissions' => $verbosePermissions,
                    'roles' => $verboseRoles,
                    'direct_permissions_count' => $directPermissionsCount,
                    'direct_roles_count' => $directRolesCount,
                    'direct_teams_count' => $directTeamsCount,
                ],
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a page of assigned entities for a model and entity.
     */
    public function searchAssignedEntitiesForModel(ModelEntitiesPageRequest $request): JsonResponse
    {
        try {
            $modelLabel = $request->validated('modelLabel');
            $modelPk = $request->validated('modelPk');
            $pageNumber = $request->validated('page');
            $entity = GatekeeperEntity::from($request->validated('entity'));
            $entityNameSearchTerm = (string) $request->validated('search_term');

            $className = $this->modelMetadataService->getClassFromLabel($modelLabel);
            $model = $this->modelService->findModelInstance($className, $modelPk);

            $modelHasEntityService = ModelHasEntityServiceFactory::create($entity);
            $paginator = $modelHasEntityService->searchAssignmentsByEntityNameForModel($model, $entityNameSearchTerm, $pageNumber);

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a page of unassigned entities for a model and entity.
     */
    public function searchUnassignedEntitiesForModel(ModelEntitiesPageRequest $request): JsonResponse
    {
        try {
            $modelLabel = $request->validated('modelLabel');
            $modelPk = $request->validated('modelPk');
            $pageNumber = $request->validated('page');
            $entity = GatekeeperEntity::from($request->validated('entity'));
            $entityNameSearchTerm = (string) $request->validated('search_term');

            $className = $this->modelMetadataService->getClassFromLabel($modelLabel);
            $model = $this->modelService->findModelInstance($className, $modelPk);

            $modelHasEntityService = ModelHasEntityServiceFactory::create($entity);
            $paginator = $modelHasEntityService->searchUnassignedByEntityNameForModel($model, $entityNameSearchTerm, $pageNumber);

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Assign an entity to a model.
     */
    public function assign(ModelEntityRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('modelLabel');
            $pk = $request->validated('modelPk');
            $entity = GatekeeperEntity::from($request->validated('entity'));
            $entityName = $request->validated('entity_name');

            $modelClass = $this->modelMetadataService->getClassFromLabel($label);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            $entityService = EntityServiceFactory::create($entity);

            if (! $entityService->exists($entityName)) {
                return $this->errorResponse(ucfirst($entity->value).' does not exist');
            }

            $entityService->assignToModel($model, $entityName);

            return Response::json([
                'message' => ucfirst($entity->value).' assigned successfully',
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Revoke an entity from a model.
     */
    public function revoke(ModelEntityRequest $request): JsonResponse
    {
        try {
            $label = $request->validated('modelLabel');
            $pk = $request->validated('modelPk');
            $entity = GatekeeperEntity::from($request->validated('entity'));
            $entityName = $request->validated('entity_name');

            $modelClass = $this->modelMetadataService->getClassFromLabel($label);
            $model = $this->modelService->findModelInstance($modelClass, $pk);

            $entityService = EntityServiceFactory::create($entity);

            if (! $entityService->exists($entityName)) {
                return $this->errorResponse(ucfirst($entity->value).' does not exist');
            }

            $entityService->revokeFromModel($model, $entityName);

            return Response::json([
                'message' => ucfirst($entity->value).' revoked successfully',
            ]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
