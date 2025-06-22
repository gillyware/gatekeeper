<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnAllTeams
{
    public function handle(Request $request, Closure $next, ...$teamNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelOnAllTeams($user, $teamNames)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
