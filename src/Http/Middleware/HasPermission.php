<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class HasPermission
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasPermission($user, $permissionName)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
