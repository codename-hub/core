<?php

namespace codename\core\model\plugin\order;

/**
 * Definition for an executable order interface that acts on the data itself
 * @package core
 */
interface executableOrderInterface
{
    /**
     * joins two data sets using the given configuration
     * @param array $data [full data set / result]
     * @return array        [new result]
     */
    public function order(array $data): array;
}
