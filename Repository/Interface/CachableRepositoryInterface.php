<?php

namespace Sruuua\Cache\Repository\Interface;

interface CachableRepositoryInterface
{
    /**
     * Get all line in table
     * 
     * @return object[] instance of query result
     */
    public function getAll();

    /**
     * Get one line by id
     *
     * @param int $id the id to search
     *  
     * @return object[] instance of query result
     */
    public function getOne(int $id);

    /**
     * Transform all data on instancied object
     * 
     * @param array $req the request resutl
     * 
     * @return array a array with all instance
     */
    public function hydrate(array $req);

    /**
     *  Create a cache name 
     * 
     * @param ?int $id = null the id of item
     * 
     * @return string the non-hash name
     */
    public function getCacheName(?int $id = null);
}
