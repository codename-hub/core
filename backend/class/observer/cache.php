<?php
namespace codename\core\observer;

/**
 * Observer Class for monitoring cache server usage
 * @package core
 * @since 2016-08-31
 */
class cache extends \codename\core\observer implements \codename\core\observer\observerInterface {

    /**
     * Contains the amount of cache gets that did not response usable data
     * @var integer
     */
    public static $miss = 0;

    /**
     * Contains the amount of hits
     * @var integer
     */
    public static $hit = 0;

    /**
     * Contains the amount of set calls
     * @var integer
     */
    public static $set = 0;

    /**
     * Contains the amount of get calls
     * @var integer
     */
    public static $get = 0;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\observer\observerInterface::update()
     */
    public function update(\codename\core\observable $observable, string $type) {
        switch($type) {
            case 'CACHE_SET':
                self::$set++;
                break;
            case 'CACHE_GET':
                self::$get++;
                break;
            case 'CACHE_HIT':
                self::$hit++;
                break;
            case 'CACHE_MISS':
                self::$miss++;
                break;
        }
    }

}
