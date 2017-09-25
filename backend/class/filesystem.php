<?php
namespace codename\core;

/**
 * We want to be capable of handling files in different storage types
 * <br />(FTP, SMB, local Filesystem, Amazon S3, Dropbox, etc.)
 * So this is our abstract filesystem class.
 * @package core
 * @since 2016-01-06
 */
abstract class filesystem implements \codename\core\filesystem\filesystemInterface {

    /**
     * Contains an instance of the errorstack class
     * @var \codename\core\errorstack
     */
    protected $errorstack = null;
    
    /**
     * Returns the error message
     * @return string
     */
    public function getError() : string {
        return $this->errormessage;
    }
    
    /**
     * Creates the errorstack instance
     * @return \codename\core\filesystem
     */
    public function __construct() {
        $this->errorstack = new \codename\core\errorstack('FILESYSTEM');
        return $this;
    }
    
}
