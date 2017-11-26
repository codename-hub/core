<?php
namespace codename\core\response;
use codename\core\app;

/**
 * I handle all the data for a CLI response
 * @package core
 * @since 2016-05-31
 */
class cli extends \codename\core\response {

  /**
   * @inheritDoc
   */
  protected function translateStatus()
  {
    $translate = array(
      self::STATUS_SUCCESS => 0,
      self::STATUS_INTERNAL_ERROR => 1,
      self::STATUS_NOTFOUND => 1 // ?
    );
    return $translate[$this->status];
  }

  /**
   * @inheritDoc
   * output to cli/console
   */
  public function pushOutput()
  {
    echo $this->getOutput();
    app::setExitCode($this->translateStatus());
  }

  /**
   * @inheritDoc
   */
  public function displayException(\Exception $e)
  {
    $formatter = new \codename\core\helper\clicolors();

    if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
      echo $formatter->getColoredString("Hicks", 'red') . chr(10);
      echo $formatter->getColoredString("{$e->getMessage()} (Code: {$e->getCode()})", 'yellow') . chr(10) . chr(10);

      if($e instanceof \codename\core\exception && !is_null($e->info)) {
        echo $formatter->getColoredString("Information", 'cyan') . chr(10);
        echo chr(10);
        print_r($e->info);
        echo chr(10);
      }

      echo $formatter->getColoredString("Stacktrace", 'cyan') . chr(10);
      echo chr(10);
      print_r($e->getTrace());
      echo chr(10);
      die();
    }

    return $value;
  }

}
