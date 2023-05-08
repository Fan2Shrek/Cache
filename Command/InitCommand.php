<?php

namespace Sruuua\Cache\Command;

use Sruuua\Application\Command;
use Sruuua\Cache\CachePool;

class InitCommand extends Command
{
    protected string $call = 'cache-clear';

    protected string $description = 'Clear all the cache';

    private CachePool $cachePool;

    public function __construct(CachePool $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function __invoke()
    {
        echo 'Clearing all cache files ....' . PHP_EOL;
        $this->cachePool->clear();
        echo 'Finish cache was deleted :thumbsup: .' . PHP_EOL;
    }
}
