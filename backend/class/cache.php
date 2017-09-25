<?php
namespace codename\core;

/**
 * Cache handling happens in this class
 * @package core
 * @since 2016-01-06
 */
abstract class cache extends \codename\core\observable implements \codename\core\cache\cacheInterface {

    /**
     * Compresses data into a JSON object, making it possible to cache objects and arrays
     * @param multitype $data
     * @return string
     */
    protected function compress($data) : string {
        return serialize($data);
    }

    /**
     * Uncompressing the JSON data object from the cache, maybe back into an array or object
     * @param string $data
     * @return multitype
     */
    protected function uncompress(string $data) {
        return unserialize($data);
    }

}
