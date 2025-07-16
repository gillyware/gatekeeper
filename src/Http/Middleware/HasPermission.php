<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasPermission extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();

        if (! $this->permissionService->modelHas($user, $permissionName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
