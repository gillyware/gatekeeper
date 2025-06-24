<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class HasAnyRole
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasAnyRole($user, $roleNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
