<?php

namespace Gillyware\Gatekeeper\Console;

use Illuminate\Console\Command;

use function Laravel\Prompts\clear;

abstract class AbstractBaseGatekeeperCommand extends Command
{
    /**
     * Clear the terminal screen.
     */
    protected function clearTerminal(): void
    {
        clear();
    }
}
