<?php
namespace codename\core\observer;

/**
 * Observer Class for monitoring internal events and measurements
 * @package core
 * @since 2016-06-09
 */
class database extends \codename\core\observer implements \codename\core\observer\observerInterface {

    /**
     * Contains the count of queries that have been executed during runtime
     * @var integer
     */
    public static $query_count = 0;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\observer\observerInterface::update()
     */
    public function update(\codename\core\observable $observable, string $type) {
        self::$query_count++;
    }

}
