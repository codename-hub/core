<?php
namespace codename\core;

/**
 * [eventHandler description]
 */
class eventHandler
{
  /**
   * the internal callable (function)
   * @var callable
   */
  protected $callable;

  /**
   *
   */
  public function __construct(callable $function)
  {
    $this->callable = $function;
  }

  /**
   * invoke the stored function in this eventhandler
   * @return multitype
   */
  public function invoke($sender, $arguments) {
    return call_user_func($this->callback, $arguments);
  }

}