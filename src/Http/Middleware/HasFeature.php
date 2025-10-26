<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasFeature extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, string $featureName)
    {
        $user = $request->user();

        if (! $this->featureService->modelHas($user, $featureName)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    public static function using(string|UnitEnum $featureName): string
    {
        $featureName = $featureName instanceof BackedEnum || $featureName instanceof UnitEnum ? (string) enum_value($featureName) : $featureName;

        return self::class.':'.$featureName;
    }
}
