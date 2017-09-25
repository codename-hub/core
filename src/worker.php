<?php
namespace codename\core;

use codename\core\validator\boolean;

/**
 * I will help you processing the tasks that exist on a queue server.
 * @package core
 * @since 2016-06-14
 */
class worker implements \codename\core\worker\workerInterface {

    /**
     * Time of seconds that will be slept between each queue element in case there are multiple results
     * @var int $pause
     */
    protected $pause = 0;

    /**
     * Time of seconds that will be slept between each queue check
     * @var int $sleep
     */
    protected $sleep = 60;

    /**
     * Contains the queue manager
     * @var \codename\core\queue $queue
     */
    protected $queue = null;

    /**
     * Contains if the current worker is paused
     * @var boolean
     */
    protected $paused = false;

    /**
     * Contains if the current worker is running
     * @var boolean
     */
    protected $running = false;

    /**
     * Create the worker instance and add options.
     * <br />Pass the <b>$queue</b> object that is responsible for the management of queue entries
     * <br />Pass an array of <b>$options</b>
     */
    public function __construct(\codename\core\queue $queue, array $options = array()) {
        $this->queue = $queue;

        $this->pause = array_key_exists('pause', $options) ? $options['pause'] : 1;
        $this->sleep = array_key_exists('sleep', $options) ? $options['sleep'] : 0;
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::start()
     */
    public function start(string $class) {
        $this->running = true;
        while($this->running) {
            $queueentry = $this->queue->load($class);
            if(count($queueentry) == 0) {
                continue;
            }
            $this->work($queueentry);
            sleep($this->sleep);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::stop()
     */
    public function stop() {

    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::pause()
     */
    public function pause() {

    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::resume()
     */
    public function resume() {

    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::skip()
     */
    public function skip() {

    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\worker_interface::work()
     */
    public function work(array $queue) {
        app::getQueue()->lock($queue['queue_class'], $queue['queue_identifier']);
        call_user_func(
            array($queue['queue_class'], $queue['queue_method']),
            array('identifier' => $queue['queue_identifier'], 'data' => json_decode($queue['queue_data']))
        );
        app::getQueue()->remove($queue['queue_id']);
        return;
    }

}
