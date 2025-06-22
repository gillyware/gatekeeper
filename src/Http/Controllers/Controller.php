<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class Controller extends BaseController
{
    protected function errorResponse(string $message, int $statusCode = HttpFoundationResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        return Response::json(['message' => $message], $statusCode);
    }
}
