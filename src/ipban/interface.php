<?php
namespace codename\core;

/**
 * Definition for \codename\core\ipban
 * @package core
 * @since 2016-04-05
 */
interface ipban_interface {

    /**
     * Performs a ban for the given ipaddress. Returns if the ip is banned.
     * @param string $ipaddress
     * @return bool
     */
    public function ban(string $ipaddress) : bool;

    /**
     * Removes the given IP address from the ban list. Returns if the ip is banned.
     * @param string $ipaddress
     * @return bool
     */
    public function unban(string $ipaddress) : bool;

    /**
     * Returns true if the given $ipaddress is banned
     * @param string $ipaddress
     * @return bool
     */
    public function isBanned(string $ipaddress) : bool;

}
