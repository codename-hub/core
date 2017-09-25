<?php
namespace codename\core;

/**
 * Observable interface
 * @package core
 * @since 2016-06-09
 */
abstract class observable implements \codename\core\observable\observableInterface {

    /**
     * Contains the observers for this instance
     * @var \codename\core\observer[]
     */
    protected $observers = array();

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\observable\observableInterface::attach()
     */
    public function attach(\codename\core\observer $observer) {
        $this->observers[] = $observer;
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\observable\observableInterface::detach()
     */
    public function detach(\codename\core\observer $observer) {
        $this->observers = array_diff($this->observers, array($observer));
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\observable\observableInterface::notify()
     */
    public function notify(string $type = '') {
        foreach($this->observers as $observer) {
            $observer->update($this, $type);
        }
        return;
    }


}
