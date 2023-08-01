<?php

namespace codename\core;

use codename\core\observable\observableInterface;

/**
 * Observable interface
 * @package core
 * @since 2016-06-09
 */
abstract class observable implements observableInterface
{
    /**
     * Contains the observers for this instance
     * @var observer[]
     */
    protected array $observers = [];

    /**
     *
     * {@inheritDoc}
     * @see observableInterface::attach
     */
    public function attach(observer $observer): void
    {
        $this->observers[] = $observer;
    }

    /**
     *
     * {@inheritDoc}
     * @see observableInterface::detach
     */
    public function detach(observer $observer): void
    {
        $this->observers = array_diff($this->observers, [$observer]);
    }

    /**
     *
     * {@inheritDoc}
     * @see observableInterface::notify
     */
    public function notify(string $type = ''): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this, $type);
        }
    }
}
