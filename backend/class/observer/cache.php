<?php

namespace codename\core\observer;

use codename\core\observable;
use codename\core\observer;

/**
 * Observer Class for monitoring cache server usage
 * @package core
 * @since 2016-08-31
 */
class cache extends observer implements observerInterface
{
    /**
     * Contains the amount of cache gets that did not response usable data
     * @var int
     */
    public static int $miss = 0;

    /**
     * Contains the amount of hits
     * @var int
     */
    public static int $hit = 0;

    /**
     * Contains the amount of set calls
     * @var int
     */
    public static int $set = 0;

    /**
     * Contains the amount of get calls
     * @var int
     */
    public static int $get = 0;

    /**
     *
     * {@inheritDoc}
     * @see observerInterface::update
     */
    public function update(observable $observable, string $type): void
    {
        switch ($type) {
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
