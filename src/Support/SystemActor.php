<?php

namespace Gillyware\Gatekeeper\Support;

use Illuminate\Database\Eloquent\Model;

class SystemActor extends Model
{
    public function getKey(): ?int
    {
        return null;
    }

    public function getMorphClass(): string
    {
        return static::class;
    }

    public function __get($key)
    {
        if ($key === 'name') {
            return 'System';
        }

        return parent::__get($key);
    }
}
