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
     * @param mixed $sender [description]
     * @param mixed $arguments [description]
     * @return mixed             [description]
     */
    public function invoke(mixed $sender, mixed $arguments): mixed
    {
        return call_user_func($this->callable, $arguments);
    }
}
