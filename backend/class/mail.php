<?php

namespace codename\core;

use codename\core\mail\mailInterface;

/**
 * Sending email and attachments.
 * @package core
 * @since 2016-02-26
 */
abstract class mail implements mailInterface
{
    /**
     * Contains the class that represents the client library's main class.
     * @var null|object $client
     */
    protected ?object $client = null;

    /**
     * Returns the mail client that was stored in the instance previously
     * @return null|object
     */
    public function getClient(): ?object
    {
        return $this->client;
    }
}
