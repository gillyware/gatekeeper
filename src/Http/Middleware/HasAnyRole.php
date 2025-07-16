<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAnyRole extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! $this->roleService->modelHasAny($user, $roleNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
