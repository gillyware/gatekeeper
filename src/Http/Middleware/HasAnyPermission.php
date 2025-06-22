<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAnyPermission
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasAnyPermission($user, $permissionNames)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
