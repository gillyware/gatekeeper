<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class HasAnyPermission
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasAnyPermission($user, $permissionNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
