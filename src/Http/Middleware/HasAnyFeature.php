<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use UnitEnum;

use function Illuminate\Support\enum_value;

class HasAnyFeature extends AbstractBaseEntityMiddleware
{
    public function handle(Request $request, Closure $next, ...$featureNames)
    {
        $user = $request->user();

        if (! $this->featureService->modelHasAny($user, $featureNames)) {
            return $this->errorResponse('Access denied.');
        }

        return $next($request);
    }

    /**
     * @param  string[]|UnitEnum[]  $featureNames
     */
    public static function using(array $featureNames): string
    {
        $implodedFeatureNames = implode(',', array_map(function (string|BackedEnum $featureName) {
            return $featureName instanceof BackedEnum || $featureName instanceof UnitEnum ? (string) enum_value($featureName) : $featureName;
        }, $featureNames));

        return self::class.':'.$implodedFeatureNames;
    }
}
