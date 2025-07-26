<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAnyFeature extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$featureNames)
    {
        $user = $request->user();

        if (! $this->featureService->modelHasAny($user, $featureNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
