<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasAllRoles extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$roleNames)
    {
        $user = $request->user();

        if (! $this->roleService->modelHasAll($user, $roleNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    /**
     * @param  string[]|UnitEnum[]  $roleNames
     */
    public static function using(array $roleNames): string
    {
        $implodedRoleNames = implode(',', array_map(function (string|BackedEnum $roleName) {
            return $roleName instanceof BackedEnum || $roleName instanceof UnitEnum ? (string) enum_value($roleName) : $roleName;
        }, $roleNames));

        return self::class.':'.$implodedRoleNames;
    }
}
