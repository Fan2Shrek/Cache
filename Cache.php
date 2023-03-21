<?php

namespace Sruuua\Cache;
// namespace Sruuua\Cache;

use Sruuua\Cache\Interface\CacheItemInterface;
use Sruuua\Cache\Interface\CachePoolInterface;
use DateInterval;

class Cache implements CacheItemInterface
{
    private string $key;

    private mixed $content;

    private bool $isHit;

    private ?\DateTimeInterface $expireDate;

    private static CachePoolInterface $pool;

    public function __construct(string $key, mixed $content, $isHit = false)
    {
        $this->key = sha1($key);
        $this->content = $content;
        $this->isHit = $isHit;
        self::$pool->addCache($this);
    }

    public static function setPool(CachePoolInterface $cachePool)
    {
        self::$pool = $cachePool;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->content;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->content = $value;

        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expireDate = $expiration;

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if (\is_int($time)) {
            $this->expireDate = $time + microtime(true);
        } elseif (null === $time) {
            $this->expireDate = null;
        } else {
            $this->expireDate = date_add($this->expireDate, $time);
        }

        return $this;
    }
}
