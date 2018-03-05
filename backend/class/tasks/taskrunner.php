<?php
namespace codename\core\tasks;

/**
 * taskrunner Interface
 */
abstract class taskrunner {

  /**
   * current task
   * @var array
   */
  protected $task = null;

  /**
   * Creates a new taskrunner
   *
   * @param array   $task [task dataset/entry]
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
  public abstract function isExecutable() : bool;

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
  public abstract function getLinkParameters() : array;

  /**
   * executes the routines
   * specific for this taskrunner
   * using the given task
   *
   * @return void
   */
  public abstract function run();

  /**
   * returns the possible outcomes
   * of running this taskrunner
   * returns an array of strings (taskrunner names)
   *
   * @return string[]
   */
  public abstract function getPossibleOutputTasks(): array;

  /**
   * lock the current task
   *
   * @return bool   [success]
   */
  protected abstract function lock() : bool;

  /**
   * unlock the current task
   *
   * @return bool   [success]
   */
  protected abstract function unlock() : bool;

  /**
   * marks a task as completed
   *
   * @return bool   [success]
   */
  protected abstract function complete() : bool;

  /**
   * returns a unique id for this taskrunner
   * @return string
   */
  protected abstract function getTaskrunnerId() : string;

  /**
   * creates a new task
   * @param array $data [task]
   */
  protected abstract function createTask(array $data);

}
