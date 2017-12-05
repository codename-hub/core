<?php
namespace codename\core\model\plugin\filter;

/**
 * Definition for an executable filter interface that acts on the data itself
 * @package core
 */
interface executableFilterInterface {

  /**
   * determines, if the given data set matches the filter or not
   * @param  array $data [single data set / row]
   * @return bool        [match success]
   */
  public function matches(array $data) : bool;

}
