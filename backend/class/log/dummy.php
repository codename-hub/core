<?php
namespace codename\core\log;

/**
 * Logging client for dummy/null output
 * @package core
 */
class dummy extends \codename\core\log implements \codename\core\log\logInterface {

  /**
   * @inheritDoc
   */
  public function write(string $text, int $level)
  {
    return;
  }

  /**
   * Contains all log instances by their file name
   * @var array of \codename\core\log
   */
  protected static $instances = array();

  /**
   * Returns the current instance by it's name
   * @param array $config
   * @return \codename\core\log
   */
  public static function getInstance(array $config) : \codename\core\log {
    if(!array_key_exists($config['data']['name'], self::$instances)) {
        self::$instances[$config['data']['name']] = new \codename\core\log\dummy($config);
    }
    return self::$instances[$config['data']['name']];
  }
}