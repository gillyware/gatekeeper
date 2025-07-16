<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnAnyTeam extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$teamNames)
    {
        $user = $request->user();

        if (! $this->teamService->modelHasAny($user, $teamNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
