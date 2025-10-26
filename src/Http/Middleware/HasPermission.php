<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasPermission extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();

        if (! $this->permissionService->modelHas($user, $permissionName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    public static function using(string|UnitEnum $permissionName): string
    {
        $permissionName = $permissionName instanceof BackedEnum || $permissionName instanceof UnitEnum ? (string) enum_value($permissionName) : $permissionName;

        return self::class.':'.$permissionName;
    }
}
