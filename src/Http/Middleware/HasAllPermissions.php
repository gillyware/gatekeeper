<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAllPermissions
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasAllPermissions') || ! $user->hasAllPermissions($permissionNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
