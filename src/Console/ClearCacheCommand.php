<?php

namespace Braxey\Gatekeeper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'gatekeeper:cache:clear';

    protected $description = 'Clear all Gatekeeper cache (permissions, roles, teams, and model-level entries)';

    public function handle(): int
    {
        $this->info('Clearing all Gatekeeper cache (including model-level)...');

        Cache::tags('gatekeeper')->flush();

        $this->newLine();
        $this->info('Gatekeeper cache fully cleared!');

        return self::SUCCESS;
    }
}
