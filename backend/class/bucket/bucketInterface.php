<?php

namespace codename\core\bucket;

use codename\core\value\text\filename;
use codename\core\value\text\filerelative;

/**
 * I am able to send files to different storage types
 * @package core
 * @since 2016-04-21
 */
interface bucketInterface
{
    /**
     * Returns true if the local $file could be pushed to $destination on the remote storage
     * @param string $localfile
     * @param string $remotefile
     * @return bool
     * @access public
     */
    public function filePush(string $localfile, string $remotefile): bool;

    /**
     * Returns true if the remote $file could be pulled to the local $file
     * @param string $remotefile
     * @param string $localfile
     * @return bool
     * @access public
     */
    public function filePull(string $remotefile, string $localfile): bool;

    /**
     * Returns true if the $remotefile exists in the bucket
     * @param string $remotefile
     * @return bool
     */
    public function fileAvailable(string $remotefile): bool;

    /**
     * Returns true if rhe given $file could be deleted from the bucket
     * @param string $remotefile
     * @return bool
     */
    public function fileDelete(string $remotefile): bool;

    /**
     * Moves a file. Can be used to rename a file
     * @param string $remotefile
     * @param string $newremotefile
     * @return bool
     */
    public function fileMove(string $remotefile, string $newremotefile): bool;

    /**
     * Returns the correct URL for the given $file
     * @param string $remotefile
     * @return string
     * @access public
     */
    public function fileGetUrl(string $remotefile): string;

    /**
     * I return an array of information about the file (if the bucket supports this feature)
     * @param string $remotefile
     * @return array
     */
    public function fileGetInfo(string $remotefile): array;

    /**
     * I return an array of elements that exist in the given $directory
     * @param string $directory
     * @return array
     */
    public function dirList(string $directory): array;

    /**
     * I will return true if the given $directory exists on the bucket
     * @param string $directory
     * @return bool
     */
    public function dirAvailable(string $directory): bool;


    /**
     * Returns true if the given $remotefile exists and actually IS a file
     * @param string $remotefile
     * @return bool
     */
    public function isFile(string $remotefile): bool;

    /**
     * Performs some checks and then downloads the file directly to the response output
     * @param filerelative $remotefile
     * @param filename $filename
     * @param array $option
     */
    public function downloadToClient(filerelative $remotefile, filename $filename, array $option = []);
}
