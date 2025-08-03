<?php

namespace Gillyware\Gatekeeper\Exceptions;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;

class GatekeeperExceptionBuilder
{
    protected string $message = 'Something went wrong.';

    public static function permissions(): GatekeeperEntityExceptionBuilder
    {
        return (new GatekeeperEntityExceptionBuilder)->setEntity(GatekeeperEntity::Permission);
    }

    public static function roles(): GatekeeperEntityExceptionBuilder
    {
        return (new GatekeeperEntityExceptionBuilder)->setEntity(GatekeeperEntity::Role);
    }

    public static function features(): GatekeeperEntityExceptionBuilder
    {
        return (new GatekeeperEntityExceptionBuilder)->setEntity(GatekeeperEntity::Feature);
    }

    public static function teams(): GatekeeperEntityExceptionBuilder
    {
        return (new GatekeeperEntityExceptionBuilder)->setEntity(GatekeeperEntity::Team);
    }

    public static function models(): GatekeeperModelExceptionBuilder
    {
        return new GatekeeperModelExceptionBuilder;
    }

    public static function console(): GatekeeperConsoleExceptionBuilder
    {
        return new GatekeeperConsoleExceptionBuilder;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function throw(): void
    {
        throw new GatekeeperException($this->message);
    }
}
