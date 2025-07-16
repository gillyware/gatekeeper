<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnAllTeams extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$teamNames)
    {
        $user = $request->user();

        if (! $this->teamService->modelHasAll($user, $teamNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
