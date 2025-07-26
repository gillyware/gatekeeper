<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasFeature extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $featureName)
    {
        $user = $request->user();

        if (! $this->featureService->modelHas($user, $featureName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
