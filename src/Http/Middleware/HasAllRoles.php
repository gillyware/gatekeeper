<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAllRoles
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasAllRoles($user, $roleNames)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
