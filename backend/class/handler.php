<?php

namespace codename\core;

/**
 * handler base class
 * this defines field and/or value handlers
 */
abstract class handler
{
    /**
     * [protected description]
     * @var null|config
     */
    protected ?config $config = null;

    /**
     * initialize a new handler using a given config
     * @param array $config [description]
     */
    public function __construct(array $config)
    {
        $this->config = new config($config);
    }

    /**
     * handles an incoming value
     * and transforms it on need
     *
     * @param  [type] $data    [description]
     * @param array $context [description]
     * @return mixed [type]          [description]
     */
    abstract public function handleValue($data, array $context): mixed;

    /**
     * handle output value
     * transform on need
     *
     * @param  [type] $data    [description]
     * @param array $context [description]
     * @return mixed [type]          [description]
     */
    abstract public function getOutput($data, array $context): mixed;
}
