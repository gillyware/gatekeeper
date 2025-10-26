<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
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

    /**
     * @param  string[]|BackedEnum[]  $teamNames
     */
    public static function using(array $teamNames): string
    {
        $implodedTeamNames = implode(',', array_map(function (string|BackedEnum $teamName) {
            return $teamName instanceof BackedEnum ? $teamName->value : $teamName;
        }, $teamNames));

        return self::class.':'.$implodedTeamNames;
    }
}
