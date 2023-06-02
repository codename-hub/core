<?php

namespace codename\core;

use codename\core\context\contextInterface;
use ReflectionException;

/**
 * These functions are used by all contexts. At least they can be overridden by their children
 * @package core
 * @since 2016-01-05
 */
class context extends bootstrapInstance implements contextInterface
{
    /**
     *
     * {@inheritDoc}
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see cache_interface::get, $key)
     */
    public function isAllowed(): bool
    {
        $identity = app::getSession()->identify();

        if (!$identity) {
            return false;
        }

        if (app::getConfig()->exists('context>' . $this->getRequest()->getData('context') . '>_security>group')) {
            return app::getAuth()->memberOf(app::getConfig()->get('context>' . $this->getRequest()->getData('context') . '>_security>group'));
        }

        return $identity;
    }
}
