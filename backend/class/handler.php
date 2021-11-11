<?php
namespace codename\core;

/**
 * handler base class
 * this defines field and/or value handlers
 */
abstract class handler {

  /**
   * [protected description]
   * @var \codename\core\config
   */
  protected $config = null;

  /**
   * initialize a new handler using a given config
   * @param array $config [description]
   */
  public function __construct(array $config)
  {
    $this->config = new \codename\core\config($config);
  }

  /**
   * handles an incoming value
   * and transforms it on need
   *
   * @param  [type] $data    [description]
   * @param  array  $context [description]
   * @return [type]          [description]
   */
  public abstract function handleValue($data, array $context);

  /**
   * handle output value
   * transform on need
   *
   * @param  [type] $data    [description]
   * @param  array  $context [description]
   * @return [type]          [description]
   */
  public abstract function getOutput($data, array $context);

}
