<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnTeam extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $teamName)
    {
        $user = $request->user();

        if (! $this->teamService->modelHas($user, $teamName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }
}
