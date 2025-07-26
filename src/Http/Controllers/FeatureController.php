<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Exceptions\GatekeeperException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Feature\StoreFeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Feature\UpdateFeaturePacket;
use Gillyware\Gatekeeper\Services\FeatureService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class FeatureController extends AbstractBaseController
{
    public function __construct(private readonly FeatureService $featureService) {}

    /**
     * Get a page of features.
     */
    public function index(EntityPagePacket $packet): HttpFoundationResponse
    {
        if (! $this->featureService->tableExists()) {
            return $this->errorResponse('The features table does not exist in the database.');
        }

        return Response::json($this->featureService->getPage($packet));
    }

    /**
     * Get a feature.
     */
    public function show(Feature $feature): HttpFoundationResponse
    {
        return Response::json($feature->toPacket());
    }

    /**
     * Create a new feature.
     */
    public function store(StoreFeaturePacket $packet): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->create($packet->name);

            return Response::json($feature, HttpFoundationResponse::HTTP_CREATED);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing feature.
     */
    public function update(UpdateFeaturePacket $packet, Feature $feature): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->update($feature, $packet->name);

            return Response::json($feature);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Turn feature off by default.
     */
    public function turnOffByDefault(Feature $feature): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->turnOffByDefault($feature);

            return Response::json($feature);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Turn feature on by default.
     */
    public function turnOnByDefault(Feature $feature): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->turnOnByDefault($feature);

            return Response::json($feature);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Deactivate a feature.
     */
    public function deactivate(Feature $feature): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->deactivate($feature);

            return Response::json($feature);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reactivate a feature.
     */
    public function reactivate(Feature $feature): HttpFoundationResponse
    {
        try {
            $feature = $this->featureService->reactivate($feature);

            return Response::json($feature);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a feature.
     */
    public function delete(Feature $feature): HttpFoundationResponse
    {
        try {
            $this->featureService->delete($feature);

            return Response::json(status: HttpFoundationResponse::HTTP_NO_CONTENT);
        } catch (GatekeeperException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
