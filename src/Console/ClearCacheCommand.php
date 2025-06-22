<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;

class ClearCacheCommand extends Command
{
    protected $signature = 'gatekeeper:clear';

    protected $description = 'Invalidate items cached by Gatekeeper';

    public function handle(CacheRepository $cacheRepository): int
    {
        $cacheRepository->clear();

        info('Gatekeeper cache cleared successfully.');

        return self::SUCCESS;
    }
}
