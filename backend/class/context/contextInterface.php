<?php
namespace codename\core\context;

/**
 * Definition for \codename\core\context
 * @package core
 * @since 2016-04-05
 */
interface contextInterface {

    /**
     * Returns true if the given security action succeeds
     * @return bool
     * @access public
     */
    public function isAllowed() : bool;

}
