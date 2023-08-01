<?php

namespace codename\core\observer;

use codename\core\observable;

/**
 * Observer Class for monitoring internal events and measurements
 * @package core
 * @since 2016-06-09
 */
interface observerInterface
{
    /**
     * This method will always be executed when the given $observable object is manipulated
     * @param observable $observable
     * @param string $type
     * @return void
     */
    public function update(observable $observable, string $type): void;
}
