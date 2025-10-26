<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

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

    public static function using(string|UnitEnum $teamName): string
    {
        $teamName = $teamName instanceof BackedEnum || $teamName instanceof UnitEnum ? (string) enum_value($teamName) : $teamName;

        return self::class.':'.$teamName;
    }
}
