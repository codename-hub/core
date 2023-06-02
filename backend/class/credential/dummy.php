<?php

namespace codename\core\credential;

use codename\core\credential;

/**
 * Dummy credential object
 */
class dummy extends credential
{
    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthentication(): mixed
    {
        return null;
    }
}
