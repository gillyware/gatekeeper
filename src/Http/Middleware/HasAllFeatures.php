<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAllFeatures extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$featureNames)
    {
        $user = $request->user();

        if (! $this->featureService->modelHasAll($user, $featureNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
