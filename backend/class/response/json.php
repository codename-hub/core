<?php
namespace codename\core\response;

/**
 * I handle all the data for a JSON response
 * @package core
 * @since 2016-05-31
 */
class json extends \codename\core\response\http {

  /**
   * @inheritDoc
   */
  public function pushOutput()
  {
    http_response_code($this->translateStatusToHttpStatus());
    echo(json_encode($this->getData()));
  }

  /**
   * @inheritDoc
   */
  public function displayException(\Exception $e)
  {
    $this->getResponse()->setStatuscode(500, "Internal Server Error");

    if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
      // TODO: optimize / check output?
      print_r(json_encode($e));
      die();
    } else {
      // TODO: show exception ?
    }

    $this->pushOutput();
  }

}
