<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasRole extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $roleName)
    {
        $user = $request->user();

        if (! $this->roleService->modelHas($user, $roleName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
