<?php
namespace codename\core;

/**
 * [event description]
 */
class event
{
  /**
   * [protected description]
   * @var string
   */
  protected $name;

  /**
   * [getName description]
   * @return [type] [description]
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * [__construct description]
   * @param string $name [description]
   */
  public function __construct(string $name)
  {
    $this->name = $name;
  }

  /**
   * invoke all event handlers without return value
   * @param  [type] $sender    [description]
   * @param  [type] $eventArgs [description]
   * @return [type]            [description]
   */
  public function invoke($sender, $eventArgs) {
    foreach($this->eventHandlers as $eventHandler) {
      $eventHandler->invoke($sender, $eventArgs);
    }
  }

  /**
   * [invokeWithResult description]
   * only the last eventHandler gets to return his return value
   * @param  [type] $sender    [description]
   * @param  [type] $eventArgs [description]
   * @return [type]            [description]
   */
  public function invokeWithResult($sender, $eventArgs) {
    $ret = null;
    foreach($this->eventHandlers as $eventHandler) {
      $ret = $eventHandler->invoke($sender, $eventArgs);
    }
    return $ret;
  }

  /**
   * [invokeWithAllResults description]
   * @param  [type] $sender    [description]
   * @param  [type] $eventArgs [description]
   * @return array
   */
  public function invokeWithAllResults($sender, $eventArgs) : array {
    $ret = array();
    foreach($this->eventHandlers as $eventHandler) {
      $ret[] = $eventHandler->invoke($sender, $eventArgs);
    }
    return $ret;
  }

  /**
   * [protected description]
   * @var eventHandler[]
   */
  protected $eventHandlers = array();

  /**
   * [addEventHandler description]
   * @param  eventHandler $eventHandler [description]
   * @return event                      [description]
   */
  public function addEventHandler(eventHandler $eventHandler) : event {
    $this->eventHandlers[] = $eventHandler;
    return $this;
  }

}