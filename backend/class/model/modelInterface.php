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
     * explicitly outputs the number of results
     * with some internal improvements
     * @return int [count of result items expected]
     */
    public function getCount() : int;

    /**
     * Deletes the given key from the model
     * @param  mixed               $primaryKey [description]
     * @return \codename\core\model            [description]
     */
    public function delete($primaryKey = null) : \codename\core\model;

    /**
     * saves the given array to the model
     * @param  array                $data [description]
     * @return \codename\core\model    [description]
     */
    public function save(array $data) : \codename\core\model;

    /**
     * Copies an entry from the component to another one
     * @param   mixed $primaryKey [the primary key's value]
     * @return \codename\core\model             [description]
     */
    public function copy($primaryKey) : \codename\core\model;

    /**
     * Loads a single entry by it's $primarykey
     * @param  mixed $primaryKey [the primary key's value]
     * @return array              [description]
     */
    public function load($primaryKey) : array;

    /**
     * Loads one single entry by using a unique-like $field to identify it by the given $value
     * @param  string $field      [description]
     * @param  string $primaryKey [description]
     * @return array              [description]
     *
     * @todo if name of key is NOT $primaryKey!
     */
    public function loadByUnique(string $field, string $primaryKey) : array;

    /**
     * [addFilter description]
     * @param  string               $field    [description]
     * @param  mixed                $value    [description]
     * @param  string               $operator [description]
     * @return \codename\core\model           [description]
     */
    public function addFilter(string $field, $value = null, string $operator = '=') : \codename\core\model;

    /**
     * Adds a filter to the query. It matches the $value in $field using the given $operator
     * @param  string               $field    [description]
     * @param  mixed                $value    [description]
     * @param  string               $operator [description]
     * @return \codename\core\model           [description]
     */
    public function addDefaultfilter(string $field, $value = null, string $operator = '=') : \codename\core\model;

     /**
      * Adds an order $field and $direction to the query.
      * @param  string               $field [description]
      * @param  string               $order [description]
      * @return \codename\core\model        [description]
      */
    public function addOrder(string $field, string $order) : \codename\core\model;

    /**
     * Enables the cache for this query
     * @param bool $cache [optional]
     * @return \codename\core\model
     */
    public function useCache() : \codename\core\model;

    /**
     * Sets the limit of the query
     * @param int $limit
     * @return \codename\core\model
     */
    public function setLimit(int $limit) : \codename\core\model;

    /**
     * Sets the offset of the query
     * @param int $offset
     * @return \codename\core\model
     */
    public function setOffset(int $offset) : \codename\core\model;

    /**
     * Enables or disables duplicate filtering
     * @param  bool                 $state [description]
     * @return \codename\core\model        [description]
     */
    public function setFilterDuplicates(bool $state) : \codename\core\model;

    /**
     * Returns the result of the given query as an array.
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
