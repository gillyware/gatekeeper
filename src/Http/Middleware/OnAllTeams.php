<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class OnAllTeams
{
    public function handle(Request $request, Closure $next, ...$teamNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelOnAllTeams($user, $teamNames)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
