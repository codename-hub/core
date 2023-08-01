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
    protected string $name;
    /**
     * [protected description]
     * @var eventHandler[]
     */
    protected array $eventHandlers = [];

    /**
     * [__construct description]
     * @param string $name [description]
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * [getName description]
     * @return string [type] [description]
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * [invokeWithResult description]
     * only the last eventHandler gets to return his return value
     * @param $sender
     * @param $eventArgs
     * @return mixed [type]            [description]
     */
    public function invokeWithResult($sender, $eventArgs): mixed
    {
        $ret = null;
        foreach ($this->eventHandlers as $eventHandler) {
            $ret = $eventHandler->invoke($sender, $eventArgs);
        }
        return $ret;
    }

    /**
     * invoke all event handlers without return value
     * @param $sender
     * @param $eventArgs
     * @return void [type]            [description]
     */
    public function invoke($sender, $eventArgs): void
    {
        foreach ($this->eventHandlers as $eventHandler) {
            $eventHandler->invoke($sender, $eventArgs);
        }
    }

    /**
     * [invokeWithAllResults description]
     * @param  [type] $sender    [description]
     * @param  [type] $eventArgs [description]
     * @return array
     */
    public function invokeWithAllResults($sender, $eventArgs): array
    {
        $ret = [];
        foreach ($this->eventHandlers as $eventHandler) {
            $ret[] = $eventHandler->invoke($sender, $eventArgs);
        }
        return $ret;
    }

    /**
     * [addEventHandler description]
     * @param eventHandler $eventHandler [description]
     * @return event                      [description]
     */
    public function addEventHandler(eventHandler $eventHandler): event
    {
        $this->eventHandlers[] = $eventHandler;
        return $this;
    }
}
