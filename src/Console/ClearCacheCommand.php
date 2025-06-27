<?php

namespace Braxey\Gatekeeper\Console;

use Braxey\Gatekeeper\Repositories\CacheRepository;
use Illuminate\Console\Command;

class ClearCacheCommand extends Command
{
    protected $signature = 'gatekeeper:cache:clear';

    protected $description = 'Clear all Gatekeeper cache (permissions, roles, teams, and model-level entries)';

    public function handle(CacheRepository $cacheRepository): int
    {
        $this->info('Clearing all Gatekeeper cache (including model-level)...');

        $cacheRepository->clear();

        $this->newLine();
        $this->info('Gatekeeper cache fully cleared!');

        return self::SUCCESS;
    }
}
