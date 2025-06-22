<?php

namespace Gillyware\Gatekeeper\Traits;

use Illuminate\Database\Eloquent\Model;

trait ActsForGatekeeper
{
    protected ?Model $actingAs;

    public function actingAs(?Model $model): static
    {
        $this->actingAs = $model;

        return $this;
    }

    protected function resolveActingAs(): void
    {
        if (! isset($this->actingAs) || ! $this->actingAs instanceof Model) {
            $this->actingAs = auth()->user();
        }
    }
}
