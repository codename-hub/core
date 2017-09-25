<?php
namespace codename\core;

/**
 * Class for adding ipbans for fail2ban
 * @package core
 * @since 2016-03-29
 */
class ipban_fail2ban extends ipban {

    /**
     * Contains the file where black IPs are listed in
     * @var string
     */
    protected $banfile = '/etc/fail2ban/ip.blacklist';

    /**
     * {@inheritDoc}
     * @see \codename\core\ipban_interface::isBanned($ipaddress)
     */
    public function isBanned(string $ipaddress) : bool {
        // Load config file
        $list = app::getFilesystem()->fileRead($this->banfile);

        // if ipaddress in file, true
        if(strpos($list, $ipaddress) !== false) {
            return true;
        }

        return false;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\ipban_interface::doBan($ipaddress)
     */
    protected function doBan(string $ipaddress) {
        $data = $this->getList() . CHR(10) . $ipaddress . ' ' . date("[m/d/Y H:m:s]");
        app::getFilesystem()->fileWrite($this->banfile, $data);
        return;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\ipban_interface::doUnban($ipaddress)
     */
    protected function doUnban(string $ipaddress) {
        return;
    }

}
