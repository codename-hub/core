<?php
namespace codename\core\tasks;

/**
 * [taskschedulerInterface description]
 */
interface taskschedulerInterface
{

  /**
   * run the scheduler (one pass)
   * should be called periodically
   * ideally, this is done on a worker instance
   * via supervisord and php as cli
   *
   * @return void
   */
  public function run();

  /**
   * returns a taskrunner for a given task
   * or null
   *
   * @param array $task [a single task to determine the taskrunner for]
   * @return taskrunner [description]
   */
  public function getTaskrunnerInstance(array $task);

}
