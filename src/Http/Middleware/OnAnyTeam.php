<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

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

    /**
     * @param  string[]|UnitEnum[]  $teamNames
     */
    public static function using(array $teamNames): string
    {
        $implodedTeamNames = implode(',', array_map(function (string|BackedEnum $teamName) {
            return $teamName instanceof BackedEnum || $teamName instanceof UnitEnum ? (string) enum_value($teamName) : $teamName;
        }, $teamNames));

        return self::class.':'.$implodedTeamNames;
    }
}
