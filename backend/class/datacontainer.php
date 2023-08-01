<?php

namespace codename\core;

use function array_key_exists;
use function strlen;

/**
 * Containing data
 * @package core
 * @since 2016-04-29
 */
class datacontainer
{
    /**
     * Contains the data of this instance
     * @var array
     */
    protected array $data = [];

    /**
     * Creates the instance and imports the $data object into this instance
     * @param array $data
     * @return datacontainer
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Adds the given KEY => VALUE array to the current set of data
     * @param array $data
     * @return void
     */
    public function addData(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Return the value of the given key. Either pass a direct name, or use a tree to navigate through the data set
     * ->get('my>config>key')
     * @param string $key
     * @return mixed
     */
    public function getData(string $key = ''): mixed
    {
        if (strlen($key) == 0) {
            return $this->data;
        }

        if (!str_contains($key, '>')) {
            if ($this->isDefined($key)) {
                return $this->data[$key];
            }
            return null;
        }

        $myConfig = $this->data;
        foreach (explode('>', $key) as $myKey) {
            if (!array_key_exists($myKey, $myConfig)) {
                return null;
            }
            $myConfig = $myConfig[$myKey];
        }

        return $myConfig;
    }

    /**
     * Stores the given $data value under the given $key in this instance's data property
     * @param string $key
     * @param mixed|null $data
     */
    public function setData(string $key, mixed $data): void
    {
        if (strlen($key) == 0) {
            return;
        }
        if (str_contains($key, '>')) {
            // NOTE/CHANGED 2020-12-07: ::setData no longer sets literal '>' keys
            // As this caused a general functionality disadvantage
            // ::isDefined returned true for already set sub-keys
            // and therefore, continued to set literals.
            $myConfig = &$this->data;
            foreach (explode('>', $key) as $myKey) {
                if ($myConfig !== null && !array_key_exists($myKey, $myConfig)) {
                    $myConfig[$myKey] = null;
                }
                $myConfig = &$myConfig[$myKey];
            }
            $myConfig = $data;
        } else {
            $this->data[$key] = $data;
        }
    }

    /**
     * Returns true if there is a value with name $key in this instance's data set
     * @param string $key
     * @return bool
     */
    public function isDefined(string $key): bool
    {
        if (!str_contains($key, '>')) {
            return array_key_exists($key, $this->data);
        } else {
            $myConfig = $this->data;
            foreach (explode('>', $key) as $myKey) {
                if (!array_key_exists($myKey, $myConfig)) {
                    return false;
                }
                $myConfig = $myConfig[$myKey];
            }
            return true;
        }
    }

    /**
     * Removes the given $key from this instance's data set
     * @param string $key
     */
    public function unsetData(string $key): void
    {
        if (strlen($key) == 0) {
            return;
        }
        if (!$this->isDefined($key)) {
            return;
        }

        unset($this->data[$key]);
    }
}
