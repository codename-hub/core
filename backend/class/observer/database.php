<?php

namespace codename\core\observer;

use codename\core\observable;
use codename\core\observer;

/**
 * Observer Class for monitoring internal events and measurements
 * @package core
 * @since 2016-06-09
 */
class database extends observer implements observerInterface
{
    /**
     * Contains the count of queries that have been executed during runtime
     * @var int
     */
    public static int $query_count = 0;

    /**
     *
     * {@inheritDoc}
     * @see observerInterface::update
     */
    public function update(observable $observable, string $type): void
    {
        self::$query_count++;
    }
}
