<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasAllPermissions extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissionNames)
    {
        $user = $request->user();

        if (! $this->permissionService->modelHasAll($user, $permissionNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    /**
     * @param  string[]|UnitEnum[]  $permissionNames
     */
    public static function using(array $permissionNames): string
    {
        $implodedPermissionNames = implode(',', array_map(function (string|BackedEnum $permissionName) {
            return $permissionName instanceof BackedEnum || $permissionName instanceof UnitEnum ? (string) enum_value($permissionName) : $permissionName;
        }, $permissionNames));

        return self::class.':'.$implodedPermissionNames;
    }
}
