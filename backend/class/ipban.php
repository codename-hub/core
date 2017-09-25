<?php
namespace codename\core;

/**
 * This class is capable of banning ip addresses for a certain amount of time.
 * <br />You have to create the clients yourself.
 * @package core
 * @since 2016-03-24
 */
abstract class ipban implements ipban_interface {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\ipban_interface::ban($ipaddress)
     */
    public function ban(string $ipaddress) : bool {
        if(!app::getValidator('text_ipv4')->isValid($ipaddress)) {
            return false;
        }

        if($this->isBanned($ipaddress)) {
            return true;
        }

        $this->doBan($ipaddress);
        return $this->isBanned($ipaddress);
    }

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\ipban_interface::unban($ipaddress)
     */
    public function unban(string $ipaddress) : bool {
        if(!app::getValidator('text_ipv4')->isValid($ipaddress)) {
            return false;
        }

        if(!$this->isBanned($ipaddress)) {
            return true;
        }

        $this->doUnban($ipaddress);

        return !$this->isBanned();
    }


    /**
     * Performs the ban of the given $ipaddress
     * @param string $ipaddress
     * @return void
     */
    protected function doBan(string $ipaddress) {
        return;
    }
    
    /**
     * Performs the unban of the given $ipaddress
     * @param string $ipaddress
     * @return void
     */
    protected function doUnban(string $ipaddress) {
        return;
    }

}
