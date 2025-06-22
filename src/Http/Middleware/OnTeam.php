<?php

namespace Braxey\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnTeam
{
    public function handle(Request $request, Closure $next, string $team)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'onTeam') || ! $user->onTeam($team)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
