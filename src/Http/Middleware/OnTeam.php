<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnTeam
{
    public function handle(Request $request, Closure $next, string $teamName)
    {
        $user = $request->user();

        if (! Gatekeeper::modelOnTeam($user, $teamName)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
