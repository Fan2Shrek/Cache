<?php

namespace Sruuua\Cache\Interface;

interface CachePoolInterface extends CacheItemPoolInterface
{
    public function addCache(CacheItemInterface $cache);
}
