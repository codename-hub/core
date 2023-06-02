<?php

namespace codename\core\model;

use codename\core\model;

/**
 * Definition for \codename\core\model
 * @package core
 * @since 2016-04-05
 */
interface modelInterface
{
    /**
     * Performs a search with the given criteria from the other functions
     * @return model
     */
    public function search(): model;

    /**
     * explicitly outputs the number of results
     * with some internal improvements
     * @return int [count of result items expected]
     */
    public function getCount(): int;

    /**
     * Deletes the given key from the model
     * @param mixed $primaryKey [description]
     * @return model            [description]
     */
    public function delete(mixed $primaryKey = null): model;

    /**
     * saves the given array to the model
     * @param array $data [description]
     * @return model    [description]
     */
    public function save(array $data): model;

    /**
     * Copies an entry from the component to another one
     * @param mixed $primaryKey [the primary key's value]
     * @return model             [description]
     */
    public function copy(mixed $primaryKey): model;

    /**
     * Loads a single entry by its $primarykey
     * @param mixed $primaryKey [the primary key's value]
     * @return array              [description]
     */
    public function load(mixed $primaryKey): array;

    /**
     * Loads one single entry by using a unique-like $field to identify it by the given $value
     * @param string $field [description]
     * @param string $value [description]
     * @return array              [description]
     *
     * @todo if name of key is NOT $primaryKey!
     */
    public function loadByUnique(string $field, string $value): array;

    /**
     * [addFilter description]
     * @param string $field [description]
     * @param mixed|null $value [description]
     * @param string $operator [description]
     * @return model           [description]
     */
    public function addFilter(string $field, mixed $value = null, string $operator = '='): model;

    /**
     * Adds a filter to the query. It matches the $value in $field using the given $operator
     * @param string $field [description]
     * @param mixed|null $value [description]
     * @param string $operator [description]
     * @return model           [description]
     */
    public function addDefaultFilter(string $field, mixed $value = null, string $operator = '='): model;

    /**
     * Adds an order $field and $direction to the query.
     * @param string $field [description]
     * @param string $order [description]
     * @return model        [description]
     */
    public function addOrder(string $field, string $order): model;

    /**
     * Enables the cache for this query
     * @return model
     */
    public function useCache(): model;

    /**
     * Sets the limit of the query
     * @param int $limit
     * @return model
     */
    public function setLimit(int $limit): model;

    /**
     * Sets the offset of the query
     * @param int $offset
     * @return model
     */
    public function setOffset(int $offset): model;

    /**
     * Enables or disables duplicate filtering
     * @param bool $state [description]
     * @return model        [description]
     */
    public function setFilterDuplicates(bool $state): model;

    /**
     * Returns the result of the given query as an array.
     * @return array
     */
    public function getResult(): array;

    /**
     * Adds a return field to the given instance
     * @param string $field
     * @return model
     */
    public function addField(string $field): model;

    /**
     * Adds a field to be hidden from the result
     * @param string $field
     * @return model
     */
    public function hideField(string $field): model;

    /**
     * Add a filter to the given instance that targets the flag field of the entries
     * @param int $flagval
     * @return model
     */
    public function withFlag(int $flagval): model;

    /**
     * Add a filter to the given instance that targets the flag field of the entries
     * and matches results that DON'T have the flag set
     * @param int $flagval
     * @return model
     */
    public function withoutFlag(int $flagval): model;

    /**
     * Add a default filter to the given instance that targets the flag field of the entries
     * @param int $flagval
     * @return model
     */
    public function withDefaultFlag(int $flagval): model;

    /**
     * Add a default filter to the given instance that targets the flag field of the entries
     * and matches results that DON'T have the flag set
     * @param int $flagval
     * @return model
     */
    public function withoutDefaultFlag(int $flagval): model;
}
