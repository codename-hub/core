<?php

namespace codename\core\tests;

use codename\core\event;
use codename\core\eventHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class for testing events and eventHandlers
 */
class eventTest extends TestCase
{
    /**
     * @return void
     */
    public function testEventInvoke(): void
    {
        $event = new event('testevent');
        static::assertEquals('testevent', $event->getName());

        $event->addEventHandler(
            new eventHandler(function ($eventArgs) {
                static::assertEquals('test', $eventArgs);
            })
        );
    }

    /**
     * @return void
     */
    public function testEventInvokeWithResult(): void
    {
        $event = new event('testevent');
        static::assertEquals('testevent', $event->getName());

        $event->addEventHandler(
            new eventHandler(function ($eventArgs) {
                static::assertEquals('test', $eventArgs);
                return 'success';
            })
        );

        $res = $event->invokeWithResult($this, 'test');
        static::assertEquals('success', $res);
    }

    /**
     * @return void
     */
    public function testEventInvokeWithAllResults(): void
    {
        $event = new event('testevent');
        static::assertEquals('testevent', $event->getName());

        $event->addEventHandler(
            new eventHandler(function ($eventArgs) {
                static::assertEquals('test', $eventArgs);
                return 'success1';
            })
        );

        $event->addEventHandler(
            new eventHandler(function ($eventArgs) {
                static::assertEquals('test', $eventArgs);
                return 'success2';
            })
        );

        $res = $event->invokeWithAllResults($this, 'test');
        static::assertEquals(['success1', 'success2'], $res);
    }
}
