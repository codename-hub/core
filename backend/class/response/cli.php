<?php
namespace codename\core\response;

/**
 * I handle all the data for a CLI response
 * @package core
 * @since 2016-05-31
 */
class cli extends \codename\core\response {

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
