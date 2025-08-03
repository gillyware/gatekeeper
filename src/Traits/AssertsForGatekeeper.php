<?php

namespace Gillyware\Gatekeeper\Traits;

use Closure;
use InvalidArgumentException;

trait AssertsForGatekeeper
{
    protected function assert(mixed $condition, Closure|string|null $onFailure = null): void
    {
        $passes = is_callable($condition)
            ? $condition()
            : (bool) $condition;

        if (! $passes) {
            if (is_string($onFailure)) {
                throw new InvalidArgumentException($onFailure);
            }

            if (is_null($onFailure)) {
                throw new InvalidArgumentException;
            }

            $onFailure();
        }
    }
}
