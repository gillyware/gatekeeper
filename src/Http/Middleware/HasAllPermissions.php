<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAllPermissions
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasAllPermissions($user, $permissionNames)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
