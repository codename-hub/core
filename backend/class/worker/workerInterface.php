<?php

namespace codename\core\worker;

interface workerInterface
{
    /**
     * I start the worker
     * @param string $class
     * @return void
     */
    public function start(string $class): void;

    /**
     * I stop the worker
     * @return void
     */
    public function stop(): void;

    /**
     * I pause the worker
     * @return void
     */
    public function pause(): void;

    /**
     * I resume the worker
     * @return void
     */
    public function resume(): void;

    /**
     * I skip the current queue entry
     * @return void
     */
    public function skip(): void;

    /**
     * I work the given entry
     * @param array $queue
     * @return void
     */
    public function work(array $queue): void;
}
