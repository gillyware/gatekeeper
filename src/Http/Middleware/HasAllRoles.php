<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAllRoles
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasAllRoles') || ! $user->hasAllRoles($roleNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
