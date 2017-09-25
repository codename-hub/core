<?php
namespace codename\core;

/**
 * Sending email and attachments.
 * @package core
 * @since 2016-02-26
 */
abstract class mail implements \codename\core\mail\mailInterface {
    
    /**
     * Contains the class that represents the client library's main class.
     * @var object $client
     */
    private $client = null;
    
    /**
     * Returns the mail client that was stored in the instance previously
     * @return object
     */
    public function getClient() {
        return $this->client;
    }

}
