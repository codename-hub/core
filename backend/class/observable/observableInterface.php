<?php

namespace codename\core\observable;

use codename\core\observer;

/**
 * Observable interface
 * @package core
 * @since 2016-06-09
 */
interface observableInterface
{
    /**
     * Add another observer to this observable class instance
     * @param observer $observer
     * @return void
     */
    public function attach(observer $observer): void;

    /**
     * Remove an observer from this observable class instance
     * @param observer $observer
     * @return void
     */
    public function detach(observer $observer): void;

    /**
     * Poll for changes in the current class instance
     * @return void
     */
    public function notify(): void;
}
