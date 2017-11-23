<?php
namespace codename\core\response;

/**
 * I handle all the data for a JSON response
 * @package core
 * @since 2016-05-31
 */
class json extends \codename\core\response {

  /**
   * @inheritDoc
   */
  public function displayException(\Exception $e)
  {
    if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
      // TODO: optimize / check output?
      print_r(json_encode($e));
      die();
    } else {
      // TODO: show exception ?
    }

  }

}
