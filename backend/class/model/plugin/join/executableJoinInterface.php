<?php
namespace codename\core\model\plugin\join;

/**
 * Definition for an executable join interface that acts on the data itself
 * @package core
 */
interface executableJoinInterface {

  /**
   * joins two data sets using the given configuration
   * @param  array $left  [full data set / result]
   * @param  array $right [full data set / result]
   * @return array        [new result]
   */
  public function join(array $left, array $right) : array;

}
