<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Closure;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();

        if (! Gatekeeper::modelHasPermission($user, $permissionName)) {
            abort(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
