<?php

namespace Sruuua\Cache;

class CacheBuilder
{
    private CachePool $cachePool;

    public function __construct()
    {
        $this->cachePool = new CachePool();
        if ($this->cachePool->isEnable())
            $this->registerExistingCache();
    }

    public static function buildFromFiles(): CachePool
    {
        $builder = new static;

        return $builder->cachePool;
    }

    /**
     * Register all cache from folder
     * 
     * @return bool true if cache files were find else false
     */
    public function registerExistingCache()
    {
        $files = array_diff(scandir(CachePool::getDirectory()), array('..', '.'));
        if (!$files) {
            return false;
        }

        foreach ($files as $file) {
            $cache = require_once(CachePool::getDirectory() . \DIRECTORY_SEPARATOR .  $file);
            $this->cachePool->addCache(unserialize($cache));
        }
    }
}
