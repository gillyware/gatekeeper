<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAnyPermission
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasAnyPermission') || ! $user->hasAnyPermission($permissionNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
