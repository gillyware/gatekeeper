<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Braxey\Gatekeeper\Facades\Gatekeeper;
use Closure;
use Illuminate\Http\Request;

class OnTeam
{
    public function handle(Request $request, Closure $next, string $teamName)
    {
        $user = $request->user();

        if (! Gatekeeper::modelOnTeam($user, $teamName)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
