<?php
namespace codename\core\tests;

use codename\core\event;
use codename\core\eventHandler;

/**
 * Class for testing events and eventHandlers
 */
class eventTest extends \PHPUnit\Framework\TestCase {

  /**
   * [testEventInvoke description]
   */
  public function testEventInvoke(): void {
    $event = new event('testevent');
    $this->assertEquals('testevent', $event->getName());

    $event->addEventHandler(new eventHandler(function($eventArgs) {
      $this->assertEquals('test', $eventArgs);
    }));

    $res = $event->invoke($this, 'test');
    $this->assertEmpty($res);
  }

  /**
   * [testEventInvokeWithResult description]
   */
  public function testEventInvokeWithResult(): void {
    $event = new event('testevent');
    $this->assertEquals('testevent', $event->getName());

    $event->addEventHandler(new eventHandler(function($eventArgs) {
      $this->assertEquals('test', $eventArgs);
      return 'success';
    }));

    $res = $event->invokeWithResult($this, 'test');
    $this->assertEquals('success', $res);
  }

  /**
   * [testEventInvokeWithAllResults description]
   */
  public function testEventInvokeWithAllResults(): void {
    $event = new event('testevent');
    $this->assertEquals('testevent', $event->getName());

    $event->addEventHandler(new eventHandler(function($eventArgs) {
      $this->assertEquals('test', $eventArgs);
      return 'success1';
    }));

    $event->addEventHandler(new eventHandler(function($eventArgs) {
      $this->assertEquals('test', $eventArgs);
      return 'success2';
    }));

    $res = $event->invokeWithAllResults($this, 'test');
    $this->assertEquals(['success1', 'success2'], $res);
  }

}
