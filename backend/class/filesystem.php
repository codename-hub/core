<?php

namespace codename\core;

use codename\core\filesystem\filesystemInterface;

/**
 * We want to be capable of handling files in different storage types
 * (FTP, SMB, local Filesystem, Amazon S3, Dropbox, etc.)
 * So this is our abstract filesystem class.
 * @package core
 * @since 2016-01-06
 */
abstract class filesystem implements filesystemInterface
{
    /**
     * Contains an instance of the errorstack class
     * @var null|errorstack
     */
    protected ?errorstack $errorstack = null;
    /**
     * @var string
     */
    protected string $errormessage;

    /**
     * Creates the errorstack instance
     * @return filesystem
     */
    public function __construct()
    {
        $this->errorstack = new errorstack('FILESYSTEM');
        return $this;
    }

    /**
     * Returns the error message
     * @return string
     */
    public function getError(): string
    {
        return $this->errormessage;
    }
}
