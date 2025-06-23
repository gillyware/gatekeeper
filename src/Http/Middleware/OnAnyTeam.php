<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnAnyTeam
{
    public function handle(Request $request, Closure $next, ...$teamNames)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'onAnyTeam') || ! $user->onAnyTeam($teamNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
