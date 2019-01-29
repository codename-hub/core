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
   * [__construct description]
   * @param callable $function [description]
   */
  public function __construct(callable $function)
  {
    $this->callable = $function;
  }

  /**
   * invoke the stored function in this eventhandler
   * @return multitype
   */
  /**
   * [invoke description]
   * @param  mixed|null  $sender    [description]
   * @param  mixed|null  $arguments [description]
   * @return mixed|null             [description]
   */
  public function invoke($sender, $arguments) {
    return call_user_func($this->callable, $arguments);
  }

}
