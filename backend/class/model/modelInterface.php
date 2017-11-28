<?php
namespace codename\core\model;

/**
 * Definition for \codename\core\model
 * @package core
 * @since 2016-04-05
 */
interface modelInterface {

    /**
     * Performs a search with the given criteria from the other functions
     * @return \codename\core\model
     */
    public function search() : \codename\core\model;

    /**
     * Deletes the given key from the model
     * @param multitype $primaryKey
     * @return \codename\core\model
     */
    public function delete($primaryKey = null) : \codename\core\model;

    /**
     * saves the given array to the model
     * @param array $primaryKey
     * @return \codename\core\model
     */
    public function save(array $data) : \codename\core\model;

    /**
     * Copies an entry from the component to another one
     * @param multitype $primaryKey
     * @return \codename\core\model
     */
    public function copy($primaryKey) : \codename\core\model;

    /**
     * Loads a single entry by it's $primarykey
     * @param multitype $primaryKey
     * @return array
     */
    public function load($primaryKey) : array;

    /**
     * Loads one single entry by using a unique-like $field to identify it by the given $value
     * @param multitype $primaryKey
     * @return array
     * @todo Name of key is NOT $primaryKey!
     */
    public function loadByUnique(string $field, string $primaryKey) : array;

    /**
     * Adds a filter to the query. It matches the $value in $field using the given $operator
     * @param multitype $primaryKey
     * @return \codename\core\model
     */
    public function addFilter(string $field, $value = null, string $operator = '=') : \codename\core\model;

    /**
     * Adds a filter to the query. It matches the $value in $field using the given $operator
     * @param multitype $primaryKey
     * @return \codename\core\model
     */
    public function addDefaultfilter(string $field, $value = null, string $operator = '=') : \codename\core\model;

    /**
     * Adds an order $field and $direction to the query.
     * @param multitype $primaryKey
     * @return \codename\core\model
     */
    public function addOrder(string $field, string $order) : \codename\core\model;

    /**
     * Enables the cache for this query
     * @param bool $cache
     * @return \codename\core\model
     */
    public function useCache() : \codename\core\model;

    /**
     * Sets the limit of the query
     * @param integer $limit
     * @return \codename\core\model
     */
    public function setLimit(int $limit) : \codename\core\model;

    /**
     * Sets the offset of the query
     * @param integer $offset
     * @return \codename\core\model
     */
    public function setOffset(int $offset) : \codename\core\model;

    /**
     * Returns the result of the given query as an array.
     * @param multitype $primaryKey
     * @return array
     */
    public function getResult() : array;

    /**
     * Adds a return field to the given instance
     * @param string $field
     * @return \codename\core\model
     */
    public function addField(string $field) : \codename\core\model;

    /**
     * Adds a field to be hidden from the result
     * @author Kevin Dargel
     * @param string $field
     * @return \codename\core\model
     */
    public function hideField(string $field) : \codename\core\model;

    /**
     * Add a filter to the given instance that targets the flag field of the entries
     * @param int $flagval
     * @return \codename\core\model
     */
    public function withFlag(int $flagval) : \codename\core\model;

    /**
     * Add a filter to the given instance that targets the flag field of the entries
     * and matches results that DON'T have the flag set
     * @author Kevin Dargel
     * @param int $flagval
     * @return \codename\core\model
     */
    public function withoutFlag(int $flagval) : \codename\core\model;

    /**
     * Add a default filter to the given instance that targets the flag field of the entries
     * @author Kevin Dargel
     * @param int $flagval
     * @return \codename\core\model
     */
    public function withDefaultFlag(int $flagval) : \codename\core\model;

    /**
     * Add a default filter to the given instance that targets the flag field of the entries
     * and matches results that DON'T have the flag set
     * @author Kevin Dargel
     * @param int $flagval
     * @return \codename\core\model
     */
    public function withoutDefaultFlag(int $flagval) : \codename\core\model;

}
