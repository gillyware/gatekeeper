<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Factories\EntityServiceFactory;
use Gillyware\Gatekeeper\Factories\ModelHasEntityServiceFactory;
use Gillyware\Gatekeeper\Packets\Models\AbstractBaseModelPacket;
use Gillyware\Gatekeeper\Packets\Models\ModelEntitiesPagePacket;
use Gillyware\Gatekeeper\Packets\Models\ModelEntityPacket;
use Gillyware\Gatekeeper\Packets\Models\ModelPagePacket;
use Gillyware\Gatekeeper\Packets\Models\ShowModelPacket;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

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
    public function index(ModelPagePacket $packet): HttpFoundationResponse
    {
        try {
            return Response::json($this->modelService->getModels($packet));
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a model and its access.
     */
    public function show(ShowModelPacket $packet): HttpFoundationResponse
    {
        try {
            $modelData = $this->modelMetadataService->getModelDataByLabel($packet->modelLabel);
            $model = $this->modelService->find($modelData, $packet->modelPk);

            $verbosePermissions = Gatekeeper::for($model)->getVerbosePermissions();
            $verboseRoles = Gatekeeper::for($model)->getVerboseRoles();
            $verboseFeatures = Gatekeeper::for($model)->getVerboseFeatures();
            $verboseTeams = Gatekeeper::for($model)->getVerboseTeams();
            $directPermissionsCount = Gatekeeper::for($model)->getDirectPermissions()->count();
            $directRolesCount = Gatekeeper::for($model)->getDirectRoles()->count();
            $directFeaturesCount = Gatekeeper::for($model)->getDirectFeatures()->count();
            $directTeamsCount = Gatekeeper::for($model)->getDirectTeams()->count();

            return Response::json(array_merge($modelData->toArray(), [
                'model_pk' => (string) $model->getKey(),
                'display' => $this->modelService->prepareModelForDisplay($modelData, $model),
                'access_sources' => [
                    'permissions' => $verbosePermissions,
                    'roles' => $verboseRoles,
                    'features' => $verboseFeatures,
                    'teams' => $verboseTeams,
                    'direct_permissions_count' => $directPermissionsCount,
                    'direct_roles_count' => $directRolesCount,
                    'direct_features_count' => $directFeaturesCount,
                    'direct_teams_count' => $directTeamsCount,
                ],
            ]));
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a page of assigned entities for a model and entity.
     */
    public function searchAssignedEntitiesForModel(ModelEntitiesPagePacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $modelHasEntityService = ModelHasEntityServiceFactory::create($packet->getEntity());
            $paginator = $modelHasEntityService->searchAssignmentsByEntityNameForModel($model, $packet);

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a page of unassigned entities for a model and entity.
     */
    public function searchUnassignedEntitiesForModel(ModelEntitiesPagePacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $modelHasEntityService = ModelHasEntityServiceFactory::create($packet->getEntity());
            $paginator = $modelHasEntityService->searchUnassignedByEntityNameForModel($model, $packet);

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a page of denied entities for a model and entity.
     */
    public function searchDeniedEntitiesForModel(ModelEntitiesPagePacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $modelHasEntityService = ModelHasEntityServiceFactory::create($packet->getEntity());
            $paginator = $modelHasEntityService->searchDeniedByEntityNameForModel($model, $packet);

            return Response::json($paginator);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Assign an entity to a model.
     */
    public function assign(ModelEntityPacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $entityService = EntityServiceFactory::create($packet->getEntity());

            if (! $entityService->exists($packet->entityName)) {
                return $this->errorResponse("{$packet->getEntity()->name} does not exist");
            }

            $entityService->assignToModel($model, $packet->entityName);

            return Response::json(['message' => "{$packet->getEntity()->name} assigned successfully"]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Unassign an entity from a model.
     */
    public function unassign(ModelEntityPacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $entityService = EntityServiceFactory::create($packet->getEntity());

            if (! $entityService->exists($packet->entityName)) {
                return $this->errorResponse("{$packet->getEntity()->name} does not exist");
            }

            $entityService->unassignFromModel($model, $packet->entityName);

            return Response::json(['message' => "{$packet->getEntity()->name} unassigned successfully"]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deny an entity from a model.
     */
    public function deny(ModelEntityPacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $entityService = EntityServiceFactory::create($packet->getEntity());

            if (! $entityService->exists($packet->entityName)) {
                return $this->errorResponse("{$packet->getEntity()->name} does not exist");
            }

            $entityService->denyFromModel($model, $packet->entityName);

            return Response::json(['message' => "{$packet->getEntity()->name} denied successfully"]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Undeny an entity from a model.
     */
    public function undeny(ModelEntityPacket $packet): HttpFoundationResponse
    {
        try {
            $model = $this->getModelFromPacket($packet);
            $entityService = EntityServiceFactory::create($packet->getEntity());

            if (! $entityService->exists($packet->entityName)) {
                return $this->errorResponse("{$packet->getEntity()->name} does not exist");
            }

            $entityService->undenyFromModel($model, $packet->entityName);

            return Response::json(['message' => "{$packet->getEntity()->name} undenied successfully"]);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function getModelFromPacket(AbstractBaseModelPacket $packet): Model
    {
        $modelData = $this->modelMetadataService->getModelDataByLabel($packet->modelLabel);

        return $this->modelService->find($modelData, $packet->modelPk);
    }
}
