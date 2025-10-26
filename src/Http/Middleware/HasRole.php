<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasRole extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $roleName)
    {
        $user = $request->user();

        if (! $this->roleService->modelHas($user, $roleName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    public static function using(string|UnitEnum $roleName): string
    {
        $roleName = $roleName instanceof BackedEnum || $roleName instanceof UnitEnum ? (string) enum_value($roleName) : $roleName;

        return self::class.':'.$roleName;
    }
}
