<?php

namespace Sruuua\Cache;

use Sruuua\Cache\Exception\InvalidArgumentException;
use Sruuua\Cache\Interface\CacheItemInterface;
use Sruuua\Cache\Interface\CachePoolInterface;
use Symfony\Component\Yaml\Yaml;
use Exception;

// namespace Sruuua\Cache;

class CachePool implements CachePoolInterface
{
    private static string $directory = '../temp/';

    private const INIT_FILE = "<?php\n\nreturn '{content}';\n";

    private bool $isEnable = true;

    /**
     * @var Cache[]
     */
    private array $cacheMap;

    /**
     * @var Cache[]
     */
    private array $persistQueue;

    public function __construct()
    {
        $this->cacheMap = array();
        Cache::setPool($this);

        if (file_exists('../config/cache.yaml')) $this->parseYaml();
        if (!is_dir(self::$directory)) {
            mkdir(self::$directory);
        }
    }

    public function parseYaml()
    {
        $yaml = Yaml::parseFile('../config/cache.yaml');
        $this->isEnable = (bool)$yaml['cache']['enable'] ?? true;
        self::$directory = $yaml['cache']['folder'] ?? '../temp';
    }

    public static function getDirectory()
    {
        return self::$directory;
    }

    /**
     * Add cache to cache map
     * 
     * @param CacheItemInterface $cache the cache to register
     * 
     * @return void
     */
    public function addCache(CacheItemInterface $cache)
    {
        if (!in_array($cache->getKey(), $this->cacheMap)) {
            $this->cacheMap[$cache->getKey()] = $cache;
        }
    }

    /**
     * Remove cache to cache map
     * 
     * @param CacheItemInterface $cache the cache to remove
     * 
     * @return void
     */
    public function removeCache(CacheItemInterface $cache)
    {
        if (in_array($cache->getKey(), $this->cacheMap)) {
            unset($this->cacheMap[$cache->getKey()]);
        }
    }

    /**
     * Validate if key respect the PSR-6 
     * 
     * @param string $key the key
     * 
     * @throws InvalidArgumentException
     * 
     * @return string the key, if its is correct
     */
    private function validateKey(string $key): string
    {
        $key = sha1($key);
        if (!preg_match('/^[a-zA-Z0-9_.-]{1,64}$/', $key)) {
            throw new InvalidArgumentException('Key value is not legal');
        }

        return $key;
    }

    public function getItem($key)
    {
        $hashedKey = $this->validateKey($key);

        return $this->cacheMap[$hashedKey] ?? new Cache($key, null);
    }

    public function getItems(array $keys = array())
    {
        $return = [];

        foreach ($keys as $key) {
            $return[] = $this->getItem($key);
        }

        return $return;
    }

    public function hasItem($key)
    {
        $hashedKey = $this->validateKey($key);

        return isset($this->cacheMap[$hashedKey]);
    }

    public function clear()
    {
        foreach ($this->cacheMap as $key => $cache) {
            $this->deleteItem($key);
        }

        if ((count(scandir(self::$directory)) == 2)) {
            rmdir(self::$directory);
            return true;
        }

        return false;
    }

    public function deleteItem($key)
    {
        $hashedKey = $this->validateKey($key);

        try {
            if ($this->hasItem($key)) {
                unlink(self::$directory . $hashedKey . '.php');
                unset($this->cacheMap[$hashedKey]);
            } elseif (file_exists(self::$directory . $key . '.php')) {
                unlink(self::$directory . $key . '.php');
                unset($this->cacheMap[$hashedKey]);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function deleteItems(array $keys)
    {
        $success = true;

        foreach ($keys as $key) {
            $success = $this->deleteItem($key) && $success;
        }

        return $success;
    }

    public function save(CacheItemInterface $cache)
    {
        try {
            $fileName = self::$directory . $cache->getKey() . '.php';
            $file = fopen($fileName, 'w') or touch($fileName);
            $content = str_replace("'", "\'", serialize($cache));
            $content = str_replace('{content}', $content, self::INIT_FILE);
            fwrite($file, $content);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof Cache) return false;

        if (!isset($this->persistQueue)) $this->persistQueue = [];

        $this->persistQueue[] = $item;
        return true;
    }

    public function commit()
    {
        $success = true;
        foreach ($this->persistQueue as $item) {
            $success = $this->save($item) && $success;
        }

        return $success;
    }

    /**
     * Get the value of isEnable
     */
    public function IsEnable()
    {
        return $this->isEnable;
    }
}
