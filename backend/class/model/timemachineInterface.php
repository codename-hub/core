<?php
namespace codename\core\model;

/**
 * enables a model to use the timemachine
 */
interface timemachineInterface {

  /**
   * determines the state of the timemachine enabled setting
   * @return bool [timemachine is enabled]
   */
  public function isTimemachineEnabled() : bool;

  /**
   * returns a valid timemachine model for this model
   * @return \codename\core\model [timemachine model]
   */
  public function getTimemachineModel() : \codename\core\model;

}
