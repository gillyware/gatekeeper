<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasPermission
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasPermission') || ! $user->hasPermission($permissionName)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
