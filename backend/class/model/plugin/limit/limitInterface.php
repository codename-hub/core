<?php
namespace codename\core\model\plugin\limit;

/**
 * Definition for \codename\core\model\plugin\limit
 * @package core
 * @since 2016-02-04
 */
interface limitInterface {

    /**
     * Creates the limit plugin and sets the important data ($limit)
     * @param int $limit
     */
    public function __CONSTRUCT(int $limit);

}
