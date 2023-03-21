<?php

namespace Sruuua\Cache\Repository;

use Sruuua\Cache\Interface\CachePoolInterface;
use Sruuua\Cache\Repository\Interface\CachableRepositoryInterface;

abstract class AbstractCachableRepository implements CachableRepositoryInterface
{
    private CachePoolInterface $cachePool;

    private string $tableName;

    private string $class;

    protected $connection;

    public function __construct(string $class, CachePoolInterface $cachePool, $connection)
    {
        $this->cachePool = $cachePool;
        $this->class = $class;
        $this->connection = $connection;
        $this->tableName = strtolower((new \ReflectionClass($this->class))->getShortName());
    }

    public function getAll()
    {
        $cache = $this->cachePool->getItem($this->getCacheName(null));
        if (null !== $content = $cache->get()) return $content;

        $req = $this->connection->prepare("SELECT * from {$this->tableName}");
        $req->execute();
        $data = $req->fetchAll();

        if (empty($data)) return [];

        $data = $this->hydrate($data);
        $cache->set($data);
        $this->cachePool->save($cache);

        return $data;
    }

    public function getOne($id)
    {
        $cache = $this->cachePool->getItem($this->getCacheName($id));
        if (null !== $content = $cache->get()) return $content;

        $req = $this->connection->prepare("SELECT * from {$this->tableName} WHERE id=?");
        $req->execute(array($id));
        $data = $req->fetchAll();

        if (empty($data)) return [];

        $data = $this->hydrate($data);
        $cache->set($data);
        $this->cachePool->save($cache);

        return $data;
    }

    /**
     * Convert array to object
     * 
     * @param array $req the array
     * 
     * @return array of instancied objet
     */
    public function hydrate(array $req)
    {
        $return = [];

        foreach ($req as $object) {
            $obj = new ($this->class)();
            foreach ($object as $key => $value) {
                if (!is_numeric($key)) {
                    $setter = 'set' . ucfirst($key);
                    $obj->$setter($value);
                }
            }
            $return[] = $obj;
        }

        return $return;
    }

    /**
     * Generated the cache name
     * 
     * @param ?int $id = null, the id of asked ressource
     * 
     * @return string
     */
    public function getCacheName(?int $id = null)
    {
        $name = strtolower((new \ReflectionClass($this->class))->getShortName()) . 's';
        if (null !== $id) {
            $name .= '|' . $id;
        }

        return $name;
    }

    /**
     * Delete the cache from the pool
     * 
     * @param string $key the key to remove
     * 
     * @return bool
     */
    public function removeCache(string $key)
    {
        return $this->cachePool->deleteItem($key);
    }

    public function handleCache(?int $id = null)
    {
        if (null !== $id) {
            $this->removeCache($this->getCacheName(null));
        }
        $this->removeCache($this->getCacheName($id));
    }
}
