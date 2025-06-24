<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class HasRole
{
    public function handle(Request $request, Closure $next, string $roleName)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasRole($user, $roleName)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
