<?php

namespace codename\core;

use codename\core\worker\workerInterface;
use ReflectionException;

/**
 * I will help you to process the tasks that exist on a queue server.
 * @package core
 * @since 2016-06-14
 */
class worker implements workerInterface
{
    /**
     * Time of seconds that will be slept between each queue element in case there are multiple results
     * @var int $pause
     */
    protected mixed $pause = 0;

    /**
     * Time of seconds that will be slept between each queue check
     * @var int $sleep
     */
    protected mixed $sleep = 60;

    /**
     * Contains the queue manager
     * @var null|queue $queue
     */
    protected ?queue $queue = null;

    /**
     * Contains if the current worker is paused
     * @var bool
     */
    protected bool $paused = false;

    /**
     * Contains if the current worker is running
     * @var bool
     */
    protected bool $running = false;

    /**
     * Create the worker instance and add options.
     * Pass the <b>$queue</b> object that is responsible for the management of queue entries
     * Pass an array of <b>$options</b>
     */
    public function __construct(queue $queue, array $options = [])
    {
        $this->queue = $queue;

        $this->pause = array_key_exists('pause', $options) ? $options['pause'] : 1;
        $this->sleep = array_key_exists('sleep', $options) ? $options['sleep'] : 0;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $class
     * @throws ReflectionException
     * @throws exception
     * @see worker_interface::start
     */
    public function start(string $class): void
    {
        $this->running = true;
        while ($this->running) {
            $queueEntry = $this->queue->load($class);
            if (count($queueEntry) == 0) {
                continue;
            }
            $this->work($queueEntry);
            sleep($this->sleep);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @param array $queue
     * @throws ReflectionException
     * @throws exception
     * @see worker_interface::work
     */
    public function work(array $queue): void
    {
        app::getQueue()->lock($queue['queue_class'], $queue['queue_identifier']);
        call_user_func(
            [$queue['queue_class'], $queue['queue_method']],
            ['identifier' => $queue['queue_identifier'], 'data' => json_decode($queue['queue_data'])]
        );
        app::getQueue()->remove($queue['queue_id']);
    }

    /**
     *
     * {@inheritDoc}
     * @see worker_interface::stop
     */
    public function stop(): void
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see worker_interface::pause
     */
    public function pause(): void
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see worker_interface::resume
     */
    public function resume(): void
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see worker_interface::skip
     */
    public function skip(): void
    {
    }
}
