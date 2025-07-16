<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAllPermissions extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! $this->permissionService->modelHasAll($user, $permissionNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
