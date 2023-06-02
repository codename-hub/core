<?php

namespace codename\core\tasks;

/**
 * taskrunner Interface
 */
abstract class taskrunner
{
    /**
     * current task
     * @var array
     */
    protected array $task;

    /**
     * Creates a new taskrunner
     *
     * @param array $task [task dataset/entry]
     */
    public function __construct(array $task)
    {
        $this->task = $task;
    }

    /**
     * determines the state if the given task/taskrunner
     * can be executed by a machine
     * you should implement a custom method here
     * that checks various parameters to determine
     * the executability, e.g.
     * - machine/server name
     * - connectivity?
     * - whatever
     *
     * @return bool
     */
    abstract public function isExecutable(): bool;

    /**
     * returns the link parameters
     * needed for human interaction
     * in case this task must be run by a human
     * this may contain a link array like
     * [
     *  'context' => 'order',
     *  'view' => 'check',
     *  'order_id' => $this->task['task_data']['order_id']
     * ]
     *
     * @return array
     */
    abstract public function getLinkParameters(): array;

    /**
     * executes the routines
     * specific for this taskrunner
     * using the given task
     *
     * @return void
     */
    abstract public function run(): void;

    /**
     * returns the possible outcomes
     * of running this taskrunner
     * returns an array of strings (taskrunner names)
     *
     * @return string[]
     */
    abstract public function getPossibleOutputTasks(): array;

    /**
     * lock the current task
     *
     * @return bool   [success]
     */
    abstract protected function lock(): bool;

    /**
     * unlock the current task
     *
     * @return bool   [success]
     */
    abstract protected function unlock(): bool;

    /**
     * marks a task as completed
     *
     * @return bool   [success]
     */
    abstract protected function complete(): bool;

    /**
     * marks a task as started/sets the start time to now
     *
     * @return bool   [success]
     */
    abstract protected function start(): bool;

    /**
     * returns a unique id for this taskrunner
     * @return string
     */
    abstract protected function getTaskrunnerId(): string;

    /**
     * creates a new task
     * @param array $data [task]
     */
    abstract protected function createTask(array $data);
}
