<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAnyPermission extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! $this->permissionService->modelHasAny($user, $permissionNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
