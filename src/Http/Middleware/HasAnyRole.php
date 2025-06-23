<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAnyRole
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasAnyRole') || ! $user->hasAnyRole($roleNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
