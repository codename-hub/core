<?php
namespace codename\core\filesystem;

/**
 * Definition for \codename\core\filesystem
 * @package core
 * @since 2016-04-05
 */
interface filesystemInterface {
    
    /**
     * Returns true if $file exists
     * @param string $file
     * @return bool
     * @access public
     */
    public function fileAvailable(string $file) : bool;

    /**
     * Returns true if $file could be deleted
     * @param string $file
     * @return bool
     * @access public
     */
    public function fileDelete(string $file) : bool;

    /**
     * Returns true if $source could be moved to $destination
     * @param string $source
     * @param string $destination
     * @return bool
     * @access public
     */
    public function fileMove(string $source, string $destination) : bool;

    /**
     * Returns true if $file could be copied to $destination
     * @param string $source
     * @param string $destination
     * @return bool
     * @access public
     */
    public function fileCopy(string $source, string $destination) : bool;

    /**
     * Returns the content of $file
     * @param string $file
     * @return string
     * @access public
     */
    public function fileRead(string $file) : string;

    /**
     * Returns true if $countent could be written to $file
     * @param string $file
     * @param string $content
     * @return bool
     * @access public
     */
    public function fileWrite(string $file, string $content=null) : bool;

    /**
     * Returns true if $directory exists
     * @param string $directory
     * @return bool
     * @access public
     */
    public function dirAvailable(string $directory) : bool;
    
    /**
     * Returns true if $directory could be created
     * @param string $directory
     * @return bool
     * @access public
     */
    public function dirCreate(string $directory) : bool;
    
    /**
     * Returns the list of objects in $directory. Returns an empty array if $directory does not exist
     * @param string $directory
     * @return array
     * @access public
     */
    public function dirList(string $directory) : array;
    
    /**
     * Returns true if $path is a directory
     * @param string $path
     * @return bool
     * @access public
     */
    public function isDirectory(string $path) : bool;
    
    /**
     * Returns an array of info about $file
     * @param string $file
     * @return array
     * @access public
     */
    public function getInfo(string $file) : array;
    

    /**
     * Returns true if the given $file actually IS a file
     * @param string $file
     * @return bool
     */
    public function isFile(string $file) : bool;
    
}
