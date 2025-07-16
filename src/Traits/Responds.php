<?php

namespace Gillyware\Gatekeeper\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

trait Responds
{
    protected function errorResponse(string $message, int $statusCode = HttpFoundationResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        return Response::json(['message' => $message], $statusCode);
    }
}
