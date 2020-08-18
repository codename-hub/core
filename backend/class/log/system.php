<?php
namespace codename\core\log;

class system extends \codename\core\log implements \codename\core\log\logInterface
{
  /**
   * Contains all log instances by their file name
   * @var \codename\core\log[]
   */
  protected static $instances = array();

  /**
   * @inheritDoc
   */
  protected function __construct(array $config)
  {
    parent::__construct($config);
    if(array_key_exists('minlevel', $config['data'])) {
        $this->minlevel = $config['data']['minlevel'];
    }
  }

  /**
   * @inheritDoc
   */
  public function write(string $text, int $level)
  {
    // only write, if ... you know.
    if($level >= $this->minlevel) {
      error_log("[LOGDRIVER:SYSTEM] ".$text, 0);
    }
  }

  /**
   * Returns the current instance by it's name
   * @param array $config
   * @return \codename\core\log
   */
  public static function getInstance(array $config) : \codename\core\log {
      if(!array_key_exists($config['data']['name'], self::$instances)) {
          self::$instances[$config['data']['name']] = new self($config);
      }
      return self::$instances[$config['data']['name']];
  }
}
