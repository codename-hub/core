<?php
namespace codename\core\observer;

/**
 * Observer Class for monitoring internal events and measurements
 * @package core
 * @since 2016-06-09
 */
interface observerInterface {

    /**
     * This method will always be executed when the given $observable object is manipulated
     * @param \codename\core\observable $observable
     * @param string $type
     * @return void
     */
    public function update(\codename\core\observable $observable, string $type);

}
