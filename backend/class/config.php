<?php

namespace codename\core;

/**
 * Store your classes' configuration in here. Use the simple methods that exist here.
 * This is my birthday app, so don't you say anything wrong!
 * @package core
 * @since 2016-04-13
 */
class config
{
    /**
     * That's where I save all my data in
     * @var array $data
     */
    protected $data = [];

    /**
     * Create your instance and pass the first key collection as an array.
     * @param array $data
     * @return config
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns true if the $key (or tree) exists in this instance's data property
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return !is_null($this->get($key));
    }

    /**
     * Return the value of the given key. Either pass a direct name, or use a tree to navigate through the data array
     * ->get('my>config>key')
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key = '', mixed $default = null): mixed
    {
        // Try returning the desired key
        if (strlen($key) == 0) {
            return $this->data;
        }

        // straight text key
        if (!str_contains($key, '>')) {
            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            }
            return $default;
        }

        // tree key
        $myConfig = $this->data;
        foreach (explode('>', $key) as $myKey) {
            if (!array_key_exists($myKey, $myConfig)) {
                return $default;
            }
            $myConfig = $myConfig[$myKey];
        }

        return $myConfig;
    }
}
