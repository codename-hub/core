<?php
namespace codename\core\frontend;
use LogicException;
use codename\core\exception;

/**
 * (DOM) Element/Content Element base class
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-05
 */
class element {

  /**
   * @var \codename\core\config
   */
  protected $config = null;
  
  /**
   *
   */
  public function __construct(array $configArray = array())
  {
    $this->config = new \codename\core\config($configArray);
  }

  public function output() : string {
    throw new LogicException("Method not implemented.");
  }



}
